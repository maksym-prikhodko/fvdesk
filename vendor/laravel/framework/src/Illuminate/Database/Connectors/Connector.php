<?php namespace Illuminate\Database\Connectors;
use PDO;
class Connector {
	protected $options = array(
		PDO::ATTR_CASE => PDO::CASE_NATURAL,
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL,
		PDO::ATTR_STRINGIFY_FETCHES => false,
		PDO::ATTR_EMULATE_PREPARES => false,
	);
	public function getOptions(array $config)
	{
		$options = array_get($config, 'options', array());
		return array_diff_key($this->options, $options) + $options;
	}
	public function createConnection($dsn, array $config, array $options)
	{
		$username = array_get($config, 'username');
		$password = array_get($config, 'password');
		return new PDO($dsn, $username, $password, $options);
	}
	public function getDefaultOptions()
	{
		return $this->options;
	}
	public function setDefaultOptions(array $options)
	{
		$this->options = $options;
	}
}
