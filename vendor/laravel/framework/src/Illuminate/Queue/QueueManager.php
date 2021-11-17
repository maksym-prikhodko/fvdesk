<?php namespace Illuminate\Queue;
use Closure;
use InvalidArgumentException;
use Illuminate\Contracts\Queue\Factory as FactoryContract;
use Illuminate\Contracts\Queue\Monitor as MonitorContract;
class QueueManager implements FactoryContract, MonitorContract {
	protected $app;
	protected $connections = array();
	public function __construct($app)
	{
		$this->app = $app;
	}
	public function looping($callback)
	{
		$this->app['events']->listen('illuminate.queue.looping', $callback);
	}
	public function failing($callback)
	{
		$this->app['events']->listen('illuminate.queue.failed', $callback);
	}
	public function stopping($callback)
	{
		$this->app['events']->listen('illuminate.queue.stopping', $callback);
	}
	public function connected($name = null)
	{
		return isset($this->connections[$name ?: $this->getDefaultDriver()]);
	}
	public function connection($name = null)
	{
		$name = $name ?: $this->getDefaultDriver();
		if ( ! isset($this->connections[$name]))
		{
			$this->connections[$name] = $this->resolve($name);
			$this->connections[$name]->setContainer($this->app);
			$this->connections[$name]->setEncrypter($this->app['encrypter']);
		}
		return $this->connections[$name];
	}
	protected function resolve($name)
	{
		$config = $this->getConfig($name);
		return $this->getConnector($config['driver'])->connect($config);
	}
	protected function getConnector($driver)
	{
		if (isset($this->connectors[$driver]))
		{
			return call_user_func($this->connectors[$driver]);
		}
		throw new InvalidArgumentException("No connector for [$driver]");
	}
	public function extend($driver, Closure $resolver)
	{
		return $this->addConnector($driver, $resolver);
	}
	public function addConnector($driver, Closure $resolver)
	{
		$this->connectors[$driver] = $resolver;
	}
	protected function getConfig($name)
	{
		return $this->app['config']["queue.connections.{$name}"];
	}
	public function getDefaultDriver()
	{
		return $this->app['config']['queue.default'];
	}
	public function setDefaultDriver($name)
	{
		$this->app['config']['queue.default'] = $name;
	}
	public function getName($connection = null)
	{
		return $connection ?: $this->getDefaultDriver();
	}
	public function isDownForMaintenance()
	{
		return $this->app->isDownForMaintenance();
	}
	public function __call($method, $parameters)
	{
		$callable = array($this->connection(), $method);
		return call_user_func_array($callable, $parameters);
	}
}
