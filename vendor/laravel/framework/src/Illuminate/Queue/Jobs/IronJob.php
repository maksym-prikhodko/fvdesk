<?php namespace Illuminate\Queue\Jobs;
use Illuminate\Queue\IronQueue;
use Illuminate\Container\Container;
use Illuminate\Contracts\Queue\Job as JobContract;
class IronJob extends Job implements JobContract {
	protected $iron;
	protected $job;
	protected $pushed = false;
	public function __construct(Container $container,
                                IronQueue $iron,
                                $job,
                                $pushed = false)
	{
		$this->job = $job;
		$this->iron = $iron;
		$this->pushed = $pushed;
		$this->container = $container;
	}
	public function fire()
	{
		$this->resolveAndFire(json_decode($this->getRawBody(), true));
	}
	public function getRawBody()
	{
		return $this->job->body;
	}
	public function delete()
	{
		parent::delete();
		if (isset($this->job->pushed)) return;
		$this->iron->deleteMessage($this->getQueue(), $this->job->id);
	}
	public function release($delay = 0)
	{
		parent::release($delay);
		if ( ! $this->pushed) $this->delete();
		$this->recreateJob($delay);
	}
	protected function recreateJob($delay)
	{
		$payload = json_decode($this->job->body, true);
		array_set($payload, 'attempts', array_get($payload, 'attempts', 1) + 1);
		$this->iron->recreate(json_encode($payload), $this->getQueue(), $delay);
	}
	public function attempts()
	{
		return array_get(json_decode($this->job->body, true), 'attempts', 1);
	}
	public function getJobId()
	{
		return $this->job->id;
	}
	public function getContainer()
	{
		return $this->container;
	}
	public function getIron()
	{
		return $this->iron;
	}
	public function getIronJob()
	{
		return $this->job;
	}
	public function getQueue()
	{
		return array_get(json_decode($this->job->body, true), 'queue');
	}
}
