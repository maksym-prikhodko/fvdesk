<?php namespace Illuminate\Queue\Jobs;
use Illuminate\Queue\RedisQueue;
use Illuminate\Container\Container;
use Illuminate\Contracts\Queue\Job as JobContract;
class RedisJob extends Job implements JobContract {
	protected $redis;
	protected $job;
	public function __construct(Container $container, RedisQueue $redis, $job, $queue)
	{
		$this->job = $job;
		$this->redis = $redis;
		$this->queue = $queue;
		$this->container = $container;
	}
	public function fire()
	{
		$this->resolveAndFire(json_decode($this->getRawBody(), true));
	}
	public function getRawBody()
	{
		return $this->job;
	}
	public function delete()
	{
		parent::delete();
		$this->redis->deleteReserved($this->queue, $this->job);
	}
	public function release($delay = 0)
	{
		parent::release($delay);
		$this->delete();
		$this->redis->release($this->queue, $this->job, $delay, $this->attempts() + 1);
	}
	public function attempts()
	{
		return array_get(json_decode($this->job, true), 'attempts');
	}
	public function getJobId()
	{
		return array_get(json_decode($this->job, true), 'id');
	}
	public function getContainer()
	{
		return $this->container;
	}
	public function getRedisQueue()
	{
		return $this->redis;
	}
	public function getRedisJob()
	{
		return $this->job;
	}
}
