<?php namespace Illuminate\Queue\Connectors;
use Illuminate\Queue\DatabaseQueue;
use Illuminate\Database\ConnectionResolverInterface;
class DatabaseConnector implements ConnectorInterface {
	protected $connections;
	public function __construct(ConnectionResolverInterface $connections)
	{
		$this->connections = $connections;
	}
	public function connect(array $config)
	{
		return new DatabaseQueue(
			$this->connections->connection(array_get($config, 'connection')),
			$config['table'],
			$config['queue'],
			array_get($config, 'expire', 60)
		);
	}
}
