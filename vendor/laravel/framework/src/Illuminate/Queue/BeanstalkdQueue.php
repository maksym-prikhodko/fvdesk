<?php namespace Illuminate\Queue;
use Pheanstalk\Pheanstalk;
use Pheanstalk\Job as PheanstalkJob;
use Illuminate\Queue\Jobs\BeanstalkdJob;
use Illuminate\Contracts\Queue\Queue as QueueContract;
class BeanstalkdQueue extends Queue implements QueueContract {
	protected $pheanstalk;
	protected $default;
	protected $timeToRun;
	public function __construct(Pheanstalk $pheanstalk, $default, $timeToRun)
	{
		$this->default = $default;
		$this->timeToRun = $timeToRun;
		$this->pheanstalk = $pheanstalk;
	}
	public function push($job, $data = '', $queue = null)
	{
		return $this->pushRaw($this->createPayload($job, $data), $queue);
	}
	public function pushRaw($payload, $queue = null, array $options = array())
	{
		return $this->pheanstalk->useTube($this->getQueue($queue))->put(
			$payload, Pheanstalk::DEFAULT_PRIORITY, Pheanstalk::DEFAULT_DELAY, $this->timeToRun
		);
	}
	public function later($delay, $job, $data = '', $queue = null)
	{
		$payload = $this->createPayload($job, $data);
		$pheanstalk = $this->pheanstalk->useTube($this->getQueue($queue));
		return $pheanstalk->put($payload, Pheanstalk::DEFAULT_PRIORITY, $this->getSeconds($delay), $this->timeToRun);
	}
	public function pop($queue = null)
	{
		$queue = $this->getQueue($queue);
		$job = $this->pheanstalk->watchOnly($queue)->reserve(0);
		if ($job instanceof PheanstalkJob)
		{
			return new BeanstalkdJob($this->container, $this->pheanstalk, $job, $queue);
		}
	}
	public function deleteMessage($queue, $id)
	{
		$this->pheanstalk->useTube($this->getQueue($queue))->delete($id);
	}
	public function getQueue($queue)
	{
		return $queue ?: $this->default;
	}
	public function getPheanstalk()
	{
		return $this->pheanstalk;
	}
}
