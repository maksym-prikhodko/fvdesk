<?php namespace Illuminate\Queue\Jobs;
use Illuminate\Queue\DatabaseQueue;
use Illuminate\Container\Container;
use Illuminate\Contracts\Queue\Job as JobContract;
class DatabaseJob extends Job implements JobContract {
	protected $database;
	protected $job;
	public function __construct(Container $container, DatabaseQueue $database, $job, $queue)
	{
		$this->job = $job;
		$this->queue = $queue;
		$this->database = $database;
		$this->container = $container;
		$this->job->attempts = $this->job->attempts + 1;
	}
	public function fire()
	{
		$this->resolveAndFire(json_decode($this->job->payload, true));
	}
	public function delete()
	{
		parent::delete();
		$this->database->deleteReserved($this->queue, $this->job->id);
	}
	public function release($delay = 0)
	{
		parent::release($delay);
		$this->delete();
		$this->database->release($this->queue, $this->job, $delay);
	}
	public function attempts()
	{
		return (int) $this->job->attempts;
	}
	public function getJobId()
	{
		return $this->job->id;
	}
	public function getRawBody()
	{
		return $this->job->payload;
	}
	public function getContainer()
	{
		return $this->container;
	}
	public function getDatabaseQueue()
	{
		return $this->database;
	}
	public function getDatabaseJob()
	{
		return $this->job;
	}
}
