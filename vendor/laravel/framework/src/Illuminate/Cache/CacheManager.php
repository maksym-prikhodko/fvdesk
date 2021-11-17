<?php namespace Illuminate\Cache;
use Closure;
use InvalidArgumentException;
use Illuminate\Contracts\Cache\Store;
use Illuminate\Contracts\Cache\Factory as FactoryContract;
class CacheManager implements FactoryContract {
	protected $app;
	protected $stores = [];
	protected $customCreators = [];
	public function __construct($app)
	{
		$this->app = $app;
	}
	public function store($name = null)
	{
		$name = $name ?: $this->getDefaultDriver();
		return $this->stores[$name] = $this->get($name);
	}
	public function driver($driver = null)
	{
		return $this->store($driver);
	}
	protected function get($name)
	{
		return isset($this->stores[$name]) ? $this->stores[$name] : $this->resolve($name);
	}
	protected function resolve($name)
	{
		$config = $this->getConfig($name);
		if (is_null($config))
		{
			throw new InvalidArgumentException("Cache store [{$name}] is not defined.");
		}
		if (isset($this->customCreators[$config['driver']]))
		{
			return $this->callCustomCreator($config);
		}
		else
		{
			return $this->{"create".ucfirst($config['driver'])."Driver"}($config);
		}
	}
	protected function callCustomCreator(array $config)
	{
		return $this->customCreators[$config['driver']]($this->app, $config);
	}
	protected function createApcDriver(array $config)
	{
		$prefix = $this->getPrefix($config);
		return $this->repository(new ApcStore(new ApcWrapper, $prefix));
	}
	protected function createArrayDriver()
	{
		return $this->repository(new ArrayStore);
	}
	protected function createFileDriver(array $config)
	{
		return $this->repository(new FileStore($this->app['files'], $config['path']));
	}
	protected function createMemcachedDriver(array $config)
	{
		$prefix = $this->getPrefix($config);
		$memcached = $this->app['memcached.connector']->connect($config['servers']);
		return $this->repository(new MemcachedStore($memcached, $prefix));
	}
	protected function createNullDriver()
	{
		return $this->repository(new NullStore);
	}
	protected function createWincacheDriver(array $config)
	{
		return $this->repository(new WinCacheStore($this->getPrefix($config)));
	}
	protected function createXcacheDriver(array $config)
	{
		return $this->repository(new XCacheStore($this->getPrefix($config)));
	}
	protected function createRedisDriver(array $config)
	{
		$redis = $this->app['redis'];
		$connection = array_get($config, 'connection', 'default') ?: 'default';
		return $this->repository(new RedisStore($redis, $this->getPrefix($config), $connection));
	}
	protected function createDatabaseDriver(array $config)
	{
		$connection = $this->app['db']->connection(array_get($config, 'connection'));
		return $this->repository(
			new DatabaseStore(
				$connection, $this->app['encrypter'], $config['table'], $this->getPrefix($config)
			)
		);
	}
	public function repository(Store $store)
	{
		$repository = new Repository($store);
		if ($this->app->bound('Illuminate\Contracts\Events\Dispatcher'))
		{
			$repository->setEventDispatcher(
				$this->app['Illuminate\Contracts\Events\Dispatcher']
			);
		}
		return $repository;
	}
	protected function getPrefix(array $config)
	{
		return array_get($config, 'prefix') ?: $this->app['config']['cache.prefix'];
	}
	protected function getConfig($name)
	{
		return $this->app['config']["cache.stores.{$name}"];
	}
	public function getDefaultDriver()
	{
		return $this->app['config']['cache.default'];
	}
	public function setDefaultDriver($name)
	{
		$this->app['config']['cache.default'] = $name;
	}
	public function extend($driver, Closure $callback)
	{
		$this->customCreators[$driver] = $callback;
		return $this;
	}
	public function __call($method, $parameters)
	{
		return call_user_func_array(array($this->store(), $method), $parameters);
	}
}
