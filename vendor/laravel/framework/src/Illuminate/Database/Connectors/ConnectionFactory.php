<?php namespace Illuminate\Database\Connectors;
use PDO;
use InvalidArgumentException;
use Illuminate\Database\MySqlConnection;
use Illuminate\Database\SQLiteConnection;
use Illuminate\Database\PostgresConnection;
use Illuminate\Database\SqlServerConnection;
use Illuminate\Contracts\Container\Container;
class ConnectionFactory {
	protected $container;
	public function __construct(Container $container)
	{
		$this->container = $container;
	}
	public function make(array $config, $name = null)
	{
		$config = $this->parseConfig($config, $name);
		if (isset($config['read']))
		{
			return $this->createReadWriteConnection($config);
		}
		return $this->createSingleConnection($config);
	}
	protected function createSingleConnection(array $config)
	{
		$pdo = $this->createConnector($config)->connect($config);
		return $this->createConnection($config['driver'], $pdo, $config['database'], $config['prefix'], $config);
	}
	protected function createReadWriteConnection(array $config)
	{
		$connection = $this->createSingleConnection($this->getWriteConfig($config));
		return $connection->setReadPdo($this->createReadPdo($config));
	}
	protected function createReadPdo(array $config)
	{
		$readConfig = $this->getReadConfig($config);
		return $this->createConnector($readConfig)->connect($readConfig);
	}
	protected function getReadConfig(array $config)
	{
		$readConfig = $this->getReadWriteConfig($config, 'read');
		return $this->mergeReadWriteConfig($config, $readConfig);
	}
	protected function getWriteConfig(array $config)
	{
		$writeConfig = $this->getReadWriteConfig($config, 'write');
		return $this->mergeReadWriteConfig($config, $writeConfig);
	}
	protected function getReadWriteConfig(array $config, $type)
	{
		if (isset($config[$type][0]))
		{
			return $config[$type][array_rand($config[$type])];
		}
		return $config[$type];
	}
	protected function mergeReadWriteConfig(array $config, array $merge)
	{
		return array_except(array_merge($config, $merge), array('read', 'write'));
	}
	protected function parseConfig(array $config, $name)
	{
		return array_add(array_add($config, 'prefix', ''), 'name', $name);
	}
	public function createConnector(array $config)
	{
		if ( ! isset($config['driver']))
		{
			throw new InvalidArgumentException("A driver must be specified.");
		}
		if ($this->container->bound($key = "db.connector.{$config['driver']}"))
		{
			return $this->container->make($key);
		}
		switch ($config['driver'])
		{
			case 'mysql':
				return new MySqlConnector;
			case 'pgsql':
				return new PostgresConnector;
			case 'sqlite':
				return new SQLiteConnector;
			case 'sqlsrv':
				return new SqlServerConnector;
		}
		throw new InvalidArgumentException("Unsupported driver [{$config['driver']}]");
	}
	protected function createConnection($driver, PDO $connection, $database, $prefix = '', array $config = array())
	{
		if ($this->container->bound($key = "db.connection.{$driver}"))
		{
			return $this->container->make($key, array($connection, $database, $prefix, $config));
		}
		switch ($driver)
		{
			case 'mysql':
				return new MySqlConnection($connection, $database, $prefix, $config);
			case 'pgsql':
				return new PostgresConnection($connection, $database, $prefix, $config);
			case 'sqlite':
				return new SQLiteConnection($connection, $database, $prefix, $config);
			case 'sqlsrv':
				return new SqlServerConnection($connection, $database, $prefix, $config);
		}
		throw new InvalidArgumentException("Unsupported driver [$driver]");
	}
}
