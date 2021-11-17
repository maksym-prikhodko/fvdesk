<?php namespace Illuminate\Queue\Connectors;
use Illuminate\Redis\Database;
use Illuminate\Queue\RedisQueue;
class RedisConnector implements ConnectorInterface {
	protected $redis;
	protected $connection;
	public function __construct(Database $redis, $connection = null)
	{
		$this->redis = $redis;
		$this->connection = $connection;
	}
	public function connect(array $config)
	{
		$queue = new RedisQueue(
			$this->redis, $config['queue'], array_get($config, 'connection', $this->connection)
		);
		$queue->setExpire(array_get($config, 'expire', 60));
		return $queue;
	}
}
