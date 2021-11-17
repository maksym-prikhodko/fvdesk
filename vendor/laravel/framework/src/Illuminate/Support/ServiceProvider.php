<?php namespace Illuminate\Support;
use BadMethodCallException;
abstract class ServiceProvider {
	protected $app;
	protected $defer = false;
	protected static $publishes = [];
	protected static $publishGroups = [];
	public function __construct($app)
	{
		$this->app = $app;
	}
	abstract public function register();
	protected function mergeConfigFrom($path, $key)
	{
		$config = $this->app['config']->get($key, []);
		$this->app['config']->set($key, array_merge(require $path, $config));
	}
	protected function loadViewsFrom($path, $namespace)
	{
		if (is_dir($appPath = $this->app->basePath().'/resources/views/vendor/'.$namespace))
		{
			$this->app['view']->addNamespace($namespace, $appPath);
		}
		$this->app['view']->addNamespace($namespace, $path);
	}
	protected function loadTranslationsFrom($path, $namespace)
	{
		$this->app['translator']->addNamespace($namespace, $path);
	}
	protected function publishes(array $paths, $group = null)
	{
		$class = get_class($this);
		if ( ! array_key_exists($class, static::$publishes))
		{
			static::$publishes[$class] = [];
		}
		static::$publishes[$class] = array_merge(static::$publishes[$class], $paths);
		if ($group)
		{
			static::$publishGroups[$group] = $paths;
		}
	}
	public static function pathsToPublish($provider = null, $group = null)
	{
		if ($group && array_key_exists($group, static::$publishGroups))
		{
			return static::$publishGroups[$group];
		}
		if ($provider && array_key_exists($provider, static::$publishes))
		{
			return static::$publishes[$provider];
		}
		if ($group || $provider)
		{
			return [];
		}
		$paths = [];
		foreach (static::$publishes as $class => $publish)
		{
			$paths = array_merge($paths, $publish);
		}
		return $paths;
	}
	public function commands($commands)
	{
		$commands = is_array($commands) ? $commands : func_get_args();
		$events = $this->app['events'];
		$events->listen('artisan.start', function($artisan) use ($commands)
		{
			$artisan->resolveCommands($commands);
		});
	}
	public function provides()
	{
		return [];
	}
	public function when()
	{
		return [];
	}
	public function isDeferred()
	{
		return $this->defer;
	}
	public static function compiles()
	{
		return [];
	}
	public function __call($method, $parameters)
	{
		if ($method == 'boot') return;
		throw new BadMethodCallException("Call to undefined method [{$method}]");
	}
}
