<?php namespace Illuminate\Redis;
use Predis\Client;
use Illuminate\Contracts\Redis\Database as DatabaseContract;
class Database implements DatabaseContract {
	protected $clients;
	public function __construct(array $servers = array())
	{
		if (isset($servers['cluster']) && $servers['cluster'])
		{
			$this->clients = $this->createAggregateClient($servers);
		}
		else
		{
			$this->clients = $this->createSingleClients($servers);
		}
	}
	protected function createAggregateClient(array $servers)
	{
		$servers = array_except($servers, array('cluster'));
		$options = $this->getClientOptions($servers);
		return array('default' => new Client(array_values($servers), $options));
	}
	protected function createSingleClients(array $servers)
	{
		$clients = array();
		$options = $this->getClientOptions($servers);
		foreach ($servers as $key => $server)
		{
			$clients[$key] = new Client($server, $options);
		}
		return $clients;
	}
	protected function getClientOptions(array $servers)
	{
		return isset($servers['options']) ? (array) $servers['options'] : [];
	}
	public function connection($name = 'default')
	{
		return $this->clients[$name ?: 'default'];
	}
	public function command($method, array $parameters = array())
	{
		return call_user_func_array(array($this->clients['default'], $method), $parameters);
	}
	public function __call($method, $parameters)
	{
		return $this->command($method, $parameters);
	}
}
