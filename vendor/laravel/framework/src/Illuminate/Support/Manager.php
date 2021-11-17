<?php namespace Illuminate\Support;
use Closure;
use InvalidArgumentException;
abstract class Manager {
	protected $app;
	protected $customCreators = array();
	protected $drivers = array();
	public function __construct($app)
	{
		$this->app = $app;
	}
	abstract public function getDefaultDriver();
	public function driver($driver = null)
	{
		$driver = $driver ?: $this->getDefaultDriver();
		if ( ! isset($this->drivers[$driver]))
		{
			$this->drivers[$driver] = $this->createDriver($driver);
		}
		return $this->drivers[$driver];
	}
	protected function createDriver($driver)
	{
		$method = 'create'.ucfirst($driver).'Driver';
		if (isset($this->customCreators[$driver]))
		{
			return $this->callCustomCreator($driver);
		}
		elseif (method_exists($this, $method))
		{
			return $this->$method();
		}
		throw new InvalidArgumentException("Driver [$driver] not supported.");
	}
	protected function callCustomCreator($driver)
	{
		return $this->customCreators[$driver]($this->app);
	}
	public function extend($driver, Closure $callback)
	{
		$this->customCreators[$driver] = $callback;
		return $this;
	}
	public function getDrivers()
	{
		return $this->drivers;
	}
	public function __call($method, $parameters)
	{
		return call_user_func_array(array($this->driver(), $method), $parameters);
	}
}
