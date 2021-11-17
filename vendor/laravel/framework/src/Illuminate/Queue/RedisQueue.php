<?php namespace Illuminate\Queue;
use Illuminate\Redis\Database;
use Illuminate\Queue\Jobs\RedisJob;
use Illuminate\Contracts\Queue\Queue as QueueContract;
class RedisQueue extends Queue implements QueueContract {
	protected $redis;
	protected $connection;
	protected $default;
	protected $expire = 60;
	public function __construct(Database $redis, $default = 'default', $connection = null)
	{
		$this->redis = $redis;
		$this->default = $default;
		$this->connection = $connection;
	}
	public function push($job, $data = '', $queue = null)
	{
		return $this->pushRaw($this->createPayload($job, $data), $queue);
	}
	public function pushRaw($payload, $queue = null, array $options = array())
	{
		$this->getConnection()->rpush($this->getQueue($queue), $payload);
		return array_get(json_decode($payload, true), 'id');
	}
	public function later($delay, $job, $data = '', $queue = null)
	{
		$payload = $this->createPayload($job, $data);
		$delay = $this->getSeconds($delay);
		$this->getConnection()->zadd($this->getQueue($queue).':delayed', $this->getTime() + $delay, $payload);
		return array_get(json_decode($payload, true), 'id');
	}
	public function release($queue, $payload, $delay, $attempts)
	{
		$payload = $this->setMeta($payload, 'attempts', $attempts);
		$this->getConnection()->zadd($this->getQueue($queue).':delayed', $this->getTime() + $delay, $payload);
	}
	public function pop($queue = null)
	{
		$original = $queue ?: $this->default;
		$queue = $this->getQueue($queue);
		if ( ! is_null($this->expire))
		{
			$this->migrateAllExpiredJobs($queue);
		}
		$job = $this->getConnection()->lpop($queue);
		if ( ! is_null($job))
		{
			$this->getConnection()->zadd($queue.':reserved', $this->getTime() + $this->expire, $job);
			return new RedisJob($this->container, $this, $job, $original);
		}
	}
	public function deleteReserved($queue, $job)
	{
		$this->getConnection()->zrem($this->getQueue($queue).':reserved', $job);
	}
	protected function migrateAllExpiredJobs($queue)
	{
		$this->migrateExpiredJobs($queue.':delayed', $queue);
		$this->migrateExpiredJobs($queue.':reserved', $queue);
	}
	public function migrateExpiredJobs($from, $to)
	{
		$options = ['cas' => true, 'watch' => $from, 'retry' => 10];
		$this->getConnection()->transaction($options, function ($transaction) use ($from, $to)
		{
			$jobs = $this->getExpiredJobs(
				$transaction, $from, $time = $this->getTime()
			);
			if (count($jobs) > 0)
			{
				$this->removeExpiredJobs($transaction, $from, $time);
				$this->pushExpiredJobsOntoNewQueue($transaction, $to, $jobs);
			}
		});
	}
	protected function getExpiredJobs($transaction, $from, $time)
	{
		return $transaction->zrangebyscore($from, '-inf', $time);
	}
	protected function removeExpiredJobs($transaction, $from, $time)
	{
		$transaction->multi();
		$transaction->zremrangebyscore($from, '-inf', $time);
	}
	protected function pushExpiredJobsOntoNewQueue($transaction, $to, $jobs)
	{
		call_user_func_array([$transaction, 'rpush'], array_merge([$to], $jobs));
	}
	protected function createPayload($job, $data = '', $queue = null)
	{
		$payload = parent::createPayload($job, $data);
		$payload = $this->setMeta($payload, 'id', $this->getRandomId());
		return $this->setMeta($payload, 'attempts', 1);
	}
	protected function getRandomId()
	{
		return str_random(32);
	}
	protected function getQueue($queue)
	{
		return 'queues:'.($queue ?: $this->default);
	}
	protected function getConnection()
	{
		return $this->redis->connection($this->connection);
	}
	public function getRedis()
	{
		return $this->redis;
	}
	public function getExpire()
	{
		return $this->expire;
	}
	public function setExpire($seconds)
	{
		$this->expire = $seconds;
	}
}
