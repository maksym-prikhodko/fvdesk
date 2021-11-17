<?php namespace Illuminate\Queue\Jobs;
use Pheanstalk\Pheanstalk;
use Illuminate\Container\Container;
use Pheanstalk\Job as PheanstalkJob;
use Illuminate\Contracts\Queue\Job as JobContract;
class BeanstalkdJob extends Job implements JobContract {
	protected $pheanstalk;
	protected $job;
	public function __construct(Container $container,
                                Pheanstalk $pheanstalk,
                                PheanstalkJob $job,
                                $queue)
	{
		$this->job = $job;
		$this->queue = $queue;
		$this->container = $container;
		$this->pheanstalk = $pheanstalk;
	}
	public function fire()
	{
		$this->resolveAndFire(json_decode($this->getRawBody(), true));
	}
	public function getRawBody()
	{
		return $this->job->getData();
	}
	public function delete()
	{
		parent::delete();
		$this->pheanstalk->delete($this->job);
	}
	public function release($delay = 0)
	{
		parent::release($delay);
		$priority = Pheanstalk::DEFAULT_PRIORITY;
		$this->pheanstalk->release($this->job, $priority, $delay);
	}
	public function bury()
	{
		$this->pheanstalk->bury($this->job);
	}
	public function attempts()
	{
		$stats = $this->pheanstalk->statsJob($this->job);
		return (int) $stats->reserves;
	}
	public function getJobId()
	{
		return $this->job->getId();
	}
	public function getContainer()
	{
		return $this->container;
	}
	public function getPheanstalk()
	{
		return $this->pheanstalk;
	}
	public function getPheanstalkJob()
	{
		return $this->job;
	}
}
