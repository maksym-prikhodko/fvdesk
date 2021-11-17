<?php namespace Illuminate\Database;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Illuminate\Database\Connectors\ConnectionFactory;
class DatabaseManager implements ConnectionResolverInterface {
	protected $app;
	protected $factory;
	protected $connections = array();
	protected $extensions = array();
	public function __construct($app, ConnectionFactory $factory)
	{
		$this->app = $app;
		$this->factory = $factory;
	}
	public function connection($name = null)
	{
		list($name, $type) = $this->parseConnectionName($name);
		if ( ! isset($this->connections[$name]))
		{
			$connection = $this->makeConnection($name);
			$this->setPdoForType($connection, $type);
			$this->connections[$name] = $this->prepare($connection);
		}
		return $this->connections[$name];
	}
	protected function parseConnectionName($name)
	{
		$name = $name ?: $this->getDefaultConnection();
		return Str::endsWith($name, ['::read', '::write'])
                            ? explode('::', $name, 2) : [$name, null];
	}
	public function purge($name = null)
	{
		$this->disconnect($name);
		unset($this->connections[$name]);
	}
	public function disconnect($name = null)
	{
		if (isset($this->connections[$name = $name ?: $this->getDefaultConnection()]))
		{
			$this->connections[$name]->disconnect();
		}
	}
	public function reconnect($name = null)
	{
		$this->disconnect($name = $name ?: $this->getDefaultConnection());
		if ( ! isset($this->connections[$name]))
		{
			return $this->connection($name);
		}
		return $this->refreshPdoConnections($name);
	}
	protected function refreshPdoConnections($name)
	{
		$fresh = $this->makeConnection($name);
		return $this->connections[$name]
                                ->setPdo($fresh->getPdo())
                                ->setReadPdo($fresh->getReadPdo());
	}
	protected function makeConnection($name)
	{
		$config = $this->getConfig($name);
		if (isset($this->extensions[$name]))
		{
			return call_user_func($this->extensions[$name], $config, $name);
		}
		$driver = $config['driver'];
		if (isset($this->extensions[$driver]))
		{
			return call_user_func($this->extensions[$driver], $config, $name);
		}
		return $this->factory->make($config, $name);
	}
	protected function prepare(Connection $connection)
	{
		$connection->setFetchMode($this->app['config']['database.fetch']);
		if ($this->app->bound('events'))
		{
			$connection->setEventDispatcher($this->app['events']);
		}
		$connection->setReconnector(function($connection)
		{
			$this->reconnect($connection->getName());
		});
		return $connection;
	}
	protected function setPdoForType(Connection $connection, $type = null)
	{
		if ($type == 'read')
		{
			$connection->setPdo($connection->getReadPdo());
		}
		elseif ($type == 'write')
		{
			$connection->setReadPdo($connection->getPdo());
		}
		return $connection;
	}
	protected function getConfig($name)
	{
		$name = $name ?: $this->getDefaultConnection();
		$connections = $this->app['config']['database.connections'];
		if (is_null($config = array_get($connections, $name)))
		{
			throw new InvalidArgumentException("Database [$name] not configured.");
		}
		return $config;
	}
	public function getDefaultConnection()
	{
		return $this->app['config']['database.default'];
	}
	public function setDefaultConnection($name)
	{
		$this->app['config']['database.default'] = $name;
	}
	public function extend($name, callable $resolver)
	{
		$this->extensions[$name] = $resolver;
	}
	public function getConnections()
	{
		return $this->connections;
	}
	public function __call($method, $parameters)
	{
		return call_user_func_array(array($this->connection(), $method), $parameters);
	}
}
