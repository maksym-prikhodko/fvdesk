<?php namespace Illuminate\Foundation;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Container\Container;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;
use Illuminate\Events\EventServiceProvider;
use Illuminate\Routing\RoutingServiceProvider;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Contracts\Foundation\Application as ApplicationContract;
class Application extends Container implements ApplicationContract, HttpKernelInterface {
	const VERSION = '5.0.28';
	protected $basePath;
	protected $hasBeenBootstrapped = false;
	protected $booted = false;
	protected $bootingCallbacks = array();
	protected $bootedCallbacks = array();
	protected $terminatingCallbacks = array();
	protected $serviceProviders = array();
	protected $loadedProviders = array();
	protected $deferredServices = array();
	protected $databasePath;
	protected $storagePath;
	protected $useStoragePathForOptimizations = false;
	protected $environmentFile = '.env';
	public function __construct($basePath = null)
	{
		$this->registerBaseBindings();
		$this->registerBaseServiceProviders();
		$this->registerCoreContainerAliases();
		if ($basePath) $this->setBasePath($basePath);
	}
	public function version()
	{
		return static::VERSION;
	}
	protected function registerBaseBindings()
	{
		static::setInstance($this);
		$this->instance('app', $this);
		$this->instance('Illuminate\Container\Container', $this);
	}
	protected function registerBaseServiceProviders()
	{
		$this->register(new EventServiceProvider($this));
		$this->register(new RoutingServiceProvider($this));
	}
	public function bootstrapWith(array $bootstrappers)
	{
		foreach ($bootstrappers as $bootstrapper)
		{
			$this['events']->fire('bootstrapping: '.$bootstrapper, [$this]);
			$this->make($bootstrapper)->bootstrap($this);
			$this['events']->fire('bootstrapped: '.$bootstrapper, [$this]);
		}
		$this->hasBeenBootstrapped = true;
	}
	public function afterLoadingEnvironment(Closure $callback)
	{
		return $this->afterBootstrapping(
			'Illuminate\Foundation\Bootstrap\DetectEnvironment', $callback
		);
	}
	public function beforeBootstrapping($bootstrapper, Closure $callback)
	{
		$this['events']->listen('bootstrapping: '.$bootstrapper, $callback);
	}
	public function afterBootstrapping($bootstrapper, Closure $callback)
	{
		$this['events']->listen('bootstrapped: '.$bootstrapper, $callback);
	}
	public function hasBeenBootstrapped()
	{
		return $this->hasBeenBootstrapped;
	}
	public function setBasePath($basePath)
	{
		$this->basePath = $basePath;
		$this->bindPathsInContainer();
		return $this;
	}
	protected function bindPathsInContainer()
	{
		$this->instance('path', $this->path());
		foreach (['base', 'config', 'database', 'lang', 'public', 'storage'] as $path)
		{
			$this->instance('path.'.$path, $this->{$path.'Path'}());
		}
	}
	public function path()
	{
		return $this->basePath.DIRECTORY_SEPARATOR.'app';
	}
	public function basePath()
	{
		return $this->basePath;
	}
	public function configPath()
	{
		return $this->basePath.DIRECTORY_SEPARATOR.'config';
	}
	public function databasePath()
	{
		return $this->databasePath ?: $this->basePath.DIRECTORY_SEPARATOR.'database';
	}
	public function useDatabasePath($path)
	{
		$this->databasePath = $path;
		$this->instance('path.database', $path);
		return $this;
	}
	public function langPath()
	{
		return $this->basePath.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'lang';
	}
	public function publicPath()
	{
		return $this->basePath.DIRECTORY_SEPARATOR.'public';
	}
	public function storagePath()
	{
		return $this->storagePath ?: $this->basePath.DIRECTORY_SEPARATOR.'storage';
	}
	public function useStoragePath($path)
	{
		$this->storagePath = $path;
		$this->instance('path.storage', $path);
		return $this;
	}
	public function loadEnvironmentFrom($file)
	{
		$this->environmentFile = $file;
		return $this;
	}
	public function environmentFile()
	{
		return $this->environmentFile ?: '.env';
	}
	public function environment()
	{
		if (func_num_args() > 0)
		{
			$patterns = is_array(func_get_arg(0)) ? func_get_arg(0) : func_get_args();
			foreach ($patterns as $pattern)
			{
				if (str_is($pattern, $this['env']))
				{
					return true;
				}
			}
			return false;
		}
		return $this['env'];
	}
	public function isLocal()
	{
		return $this['env'] == 'local';
	}
	public function detectEnvironment(Closure $callback)
	{
		$args = isset($_SERVER['argv']) ? $_SERVER['argv'] : null;
		return $this['env'] = (new EnvironmentDetector())->detect($callback, $args);
	}
	public function runningInConsole()
	{
		return php_sapi_name() == 'cli';
	}
	public function runningUnitTests()
	{
		return $this['env'] == 'testing';
	}
	public function registerConfiguredProviders()
	{
		$manifestPath = $this->getCachedServicesPath();
		(new ProviderRepository($this, new Filesystem, $manifestPath))
					->load($this->config['app.providers']);
	}
	public function register($provider, $options = array(), $force = false)
	{
		if ($registered = $this->getProvider($provider) && ! $force)
		{
			return $registered;
		}
		if (is_string($provider))
		{
			$provider = $this->resolveProviderClass($provider);
		}
		$provider->register();
		foreach ($options as $key => $value)
		{
			$this[$key] = $value;
		}
		$this->markAsRegistered($provider);
		if ($this->booted)
		{
			$this->bootProvider($provider);
		}
		return $provider;
	}
	public function getProvider($provider)
	{
		$name = is_string($provider) ? $provider : get_class($provider);
		return array_first($this->serviceProviders, function($key, $value) use ($name)
		{
			return $value instanceof $name;
		});
	}
	public function resolveProviderClass($provider)
	{
		return new $provider($this);
	}
	protected function markAsRegistered($provider)
	{
		$this['events']->fire($class = get_class($provider), array($provider));
		$this->serviceProviders[] = $provider;
		$this->loadedProviders[$class] = true;
	}
	public function loadDeferredProviders()
	{
		foreach ($this->deferredServices as $service => $provider)
		{
			$this->loadDeferredProvider($service);
		}
		$this->deferredServices = array();
	}
	public function loadDeferredProvider($service)
	{
		if ( ! isset($this->deferredServices[$service]))
		{
			return;
		}
		$provider = $this->deferredServices[$service];
		if ( ! isset($this->loadedProviders[$provider]))
		{
			$this->registerDeferredProvider($provider, $service);
		}
	}
	public function registerDeferredProvider($provider, $service = null)
	{
		if ($service) unset($this->deferredServices[$service]);
		$this->register($instance = new $provider($this));
		if ( ! $this->booted)
		{
			$this->booting(function() use ($instance)
			{
				$this->bootProvider($instance);
			});
		}
	}
	public function make($abstract, $parameters = array())
	{
		$abstract = $this->getAlias($abstract);
		if (isset($this->deferredServices[$abstract]))
		{
			$this->loadDeferredProvider($abstract);
		}
		return parent::make($abstract, $parameters);
	}
	public function bound($abstract)
	{
		return isset($this->deferredServices[$abstract]) || parent::bound($abstract);
	}
	public function isBooted()
	{
		return $this->booted;
	}
	public function boot()
	{
		if ($this->booted) return;
		$this->fireAppCallbacks($this->bootingCallbacks);
		array_walk($this->serviceProviders, function($p) {
			$this->bootProvider($p);
		});
		$this->booted = true;
		$this->fireAppCallbacks($this->bootedCallbacks);
	}
	protected function bootProvider(ServiceProvider $provider)
	{
		if (method_exists($provider, 'boot'))
		{
			return $this->call([$provider, 'boot']);
		}
	}
	public function booting($callback)
	{
		$this->bootingCallbacks[] = $callback;
	}
	public function booted($callback)
	{
		$this->bootedCallbacks[] = $callback;
		if ($this->isBooted()) $this->fireAppCallbacks(array($callback));
	}
	public function handle(SymfonyRequest $request, $type = self::MASTER_REQUEST, $catch = true)
	{
		return $this['Illuminate\Contracts\Http\Kernel']->handle(Request::createFromBase($request));
	}
	public function configurationIsCached()
	{
		return $this['files']->exists($this->getCachedConfigPath());
	}
	public function getCachedConfigPath()
	{
		if ($this->vendorIsWritableForOptimizations())
		{
			return $this->basePath().'/vendor/config.php';
		}
		else
		{
			return $this['path.storage'].'/framework/config.php';
		}
	}
	public function routesAreCached()
	{
		return $this['files']->exists($this->getCachedRoutesPath());
	}
	public function getCachedRoutesPath()
	{
		if ($this->vendorIsWritableForOptimizations())
		{
			return $this->basePath().'/vendor/routes.php';
		}
		else
		{
			return $this['path.storage'].'/framework/routes.php';
		}
	}
	public function getCachedCompilePath()
	{
		if ($this->vendorIsWritableForOptimizations())
		{
			return $this->basePath().'/vendor/compiled.php';
		}
		else
		{
			return $this->storagePath().'/framework/compiled.php';
		}
	}
	public function getCachedServicesPath()
	{
		if ($this->vendorIsWritableForOptimizations())
		{
			return $this->basePath().'/vendor/services.json';
		}
		else
		{
			return $this->storagePath().'/framework/services.json';
		}
	}
	public function vendorIsWritableForOptimizations()
	{
		if ($this->useStoragePathForOptimizations) return false;
		return is_writable($this->basePath().'/vendor');
	}
	public function useStoragePathForOptimizations($value = true)
	{
		$this->useStoragePathForOptimizations = $value;
		return $this;
	}
	protected function fireAppCallbacks(array $callbacks)
	{
		foreach ($callbacks as $callback)
		{
			call_user_func($callback, $this);
		}
	}
	public function isDownForMaintenance()
	{
		return file_exists($this->storagePath().DIRECTORY_SEPARATOR.'framework'.DIRECTORY_SEPARATOR.'down');
	}
	public function down(Closure $callback)
	{
		$this['events']->listen('illuminate.app.down', $callback);
	}
	public function abort($code, $message = '', array $headers = array())
	{
		if ($code == 404)
		{
			throw new NotFoundHttpException($message);
		}
		throw new HttpException($code, $message, null, $headers);
	}
	public function terminating(Closure $callback)
	{
		$this->terminatingCallbacks[] = $callback;
		return $this;
	}
	public function terminate()
	{
		foreach ($this->terminatingCallbacks as $terminating)
		{
			$this->call($terminating);
		}
	}
	public function getLoadedProviders()
	{
		return $this->loadedProviders;
	}
	public function getDeferredServices()
	{
		return $this->deferredServices;
	}
	public function setDeferredServices(array $services)
	{
		$this->deferredServices = $services;
	}
	public function addDeferredServices(array $services)
	{
		$this->deferredServices = array_merge($this->deferredServices, $services);
	}
	public function isDeferredService($service)
	{
		return isset($this->deferredServices[$service]);
	}
	public function getLocale()
	{
		return $this['config']->get('app.locale');
	}
	public function setLocale($locale)
	{
		$this['config']->set('app.locale', $locale);
		$this['translator']->setLocale($locale);
		$this['events']->fire('locale.changed', array($locale));
	}
	public function registerCoreContainerAliases()
	{
		$aliases = array(
			'app'                  => ['Illuminate\Foundation\Application', 'Illuminate\Contracts\Container\Container', 'Illuminate\Contracts\Foundation\Application'],
			'artisan'              => ['Illuminate\Console\Application', 'Illuminate\Contracts\Console\Application'],
			'auth'                 => 'Illuminate\Auth\AuthManager',
			'auth.driver'          => ['Illuminate\Auth\Guard', 'Illuminate\Contracts\Auth\Guard'],
			'auth.password.tokens' => 'Illuminate\Auth\Passwords\TokenRepositoryInterface',
			'blade.compiler'       => 'Illuminate\View\Compilers\BladeCompiler',
			'cache'                => ['Illuminate\Cache\CacheManager', 'Illuminate\Contracts\Cache\Factory'],
			'cache.store'          => ['Illuminate\Cache\Repository', 'Illuminate\Contracts\Cache\Repository'],
			'config'               => ['Illuminate\Config\Repository', 'Illuminate\Contracts\Config\Repository'],
			'cookie'               => ['Illuminate\Cookie\CookieJar', 'Illuminate\Contracts\Cookie\Factory', 'Illuminate\Contracts\Cookie\QueueingFactory'],
			'encrypter'            => ['Illuminate\Encryption\Encrypter', 'Illuminate\Contracts\Encryption\Encrypter'],
			'db'                   => 'Illuminate\Database\DatabaseManager',
			'events'               => ['Illuminate\Events\Dispatcher', 'Illuminate\Contracts\Events\Dispatcher'],
			'files'                => 'Illuminate\Filesystem\Filesystem',
			'filesystem'           => ['Illuminate\Filesystem\FilesystemManager', 'Illuminate\Contracts\Filesystem\Factory'],
			'filesystem.disk'      => 'Illuminate\Contracts\Filesystem\Filesystem',
			'filesystem.cloud'     => 'Illuminate\Contracts\Filesystem\Cloud',
			'hash'                 => 'Illuminate\Contracts\Hashing\Hasher',
			'translator'           => ['Illuminate\Translation\Translator', 'Symfony\Component\Translation\TranslatorInterface'],
			'log'                  => ['Illuminate\Log\Writer', 'Illuminate\Contracts\Logging\Log', 'Psr\Log\LoggerInterface'],
			'mailer'               => ['Illuminate\Mail\Mailer', 'Illuminate\Contracts\Mail\Mailer', 'Illuminate\Contracts\Mail\MailQueue'],
			'paginator'            => 'Illuminate\Pagination\Factory',
			'auth.password'        => ['Illuminate\Auth\Passwords\PasswordBroker', 'Illuminate\Contracts\Auth\PasswordBroker'],
			'queue'                => ['Illuminate\Queue\QueueManager', 'Illuminate\Contracts\Queue\Factory', 'Illuminate\Contracts\Queue\Monitor'],
			'queue.connection'     => 'Illuminate\Contracts\Queue\Queue',
			'redirect'             => 'Illuminate\Routing\Redirector',
			'redis'                => ['Illuminate\Redis\Database', 'Illuminate\Contracts\Redis\Database'],
			'request'              => 'Illuminate\Http\Request',
			'router'               => ['Illuminate\Routing\Router', 'Illuminate\Contracts\Routing\Registrar'],
			'session'              => 'Illuminate\Session\SessionManager',
			'session.store'        => ['Illuminate\Session\Store', 'Symfony\Component\HttpFoundation\Session\SessionInterface'],
			'url'                  => ['Illuminate\Routing\UrlGenerator', 'Illuminate\Contracts\Routing\UrlGenerator'],
			'validator'            => ['Illuminate\Validation\Factory', 'Illuminate\Contracts\Validation\Factory'],
			'view'                 => ['Illuminate\View\Factory', 'Illuminate\Contracts\View\Factory'],
		);
		foreach ($aliases as $key => $aliases)
		{
			foreach ((array) $aliases as $alias)
			{
				$this->alias($key, $alias);
			}
		}
	}
	public function flush()
	{
		parent::flush();
		$this->loadedProviders = [];
	}
}
