<?php namespace Illuminate\Database\Connectors;
use PDO;
class SqlServerConnector extends Connector implements ConnectorInterface {
	protected $options = array(
		PDO::ATTR_CASE => PDO::CASE_NATURAL,
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL,
		PDO::ATTR_STRINGIFY_FETCHES => false,
	);
	public function connect(array $config)
	{
		$options = $this->getOptions($config);
		return $this->createConnection($this->getDsn($config), $config, $options);
	}
	protected function getDsn(array $config)
	{
		if (in_array('dblib', $this->getAvailableDrivers()))
		{
			return $this->getDblibDsn($config);
		}
		else
		{
			return $this->getSqlSrvDsn($config);
		}
	}
	protected function getDblibDsn(array $config)
	{
		$arguments = array(
			'host' => $this->buildHostString($config, ':'),
			'dbname' => $config['database']
		);
		$arguments = array_merge(
			$arguments, array_only($config, ['appname', 'charset'])
		);
		return $this->buildConnectString('dblib', $arguments);
	}
	protected function getSqlSrvDsn(array $config)
	{
		$arguments = array(
			'Server' => $this->buildHostString($config, ',')
		);
		if (isset($config['database'])) {
			$arguments['Database'] = $config['database'];
		}
		if (isset($config['appname'])) {
			$arguments['APP'] = $config['appname'];
		}
		return $this->buildConnectString('sqlsrv', $arguments);
	}
	protected function buildConnectString($driver, array $arguments)
	{
		$options = array_map(function($key) use ($arguments)
		{
			return sprintf("%s=%s", $key, $arguments[$key]);
		}, array_keys($arguments));
		return $driver.":".implode(';', $options);
	}
	protected function buildHostString(array $config, $separator)
	{
		if(isset($config['port']))
		{
			return $config['host'].$separator.$config['port'];
		}
		else
		{
			return $config['host'];
		}
	}
	protected function getAvailableDrivers()
	{
		return PDO::getAvailableDrivers();
	}
}
