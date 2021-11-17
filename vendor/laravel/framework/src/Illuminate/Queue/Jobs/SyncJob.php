<?php namespace Illuminate\Queue\Jobs;
use Illuminate\Container\Container;
use Illuminate\Contracts\Queue\Job as JobContract;
class SyncJob extends Job implements JobContract {
	protected $job;
	protected $payload;
	public function __construct(Container $container, $payload)
	{
		$this->payload = $payload;
		$this->container = $container;
	}
	public function fire()
	{
		$this->resolveAndFire(json_decode($this->payload, true));
	}
	public function getRawBody()
	{
		return $this->payload;
	}
	public function release($delay = 0)
	{
		parent::release($delay);
	}
	public function attempts()
	{
		return 1;
	}
	public function getJobId()
	{
		return '';
	}
}
