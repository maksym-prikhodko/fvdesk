<?php namespace Illuminate\Queue;
use Illuminate\Contracts\Queue\Job as JobContract;
trait InteractsWithQueue {
	protected $job;
	public function delete()
	{
		if ($this->job)
		{
			return $this->job->delete();
		}
	}
	public function release($delay = 0)
	{
		if ($this->job)
		{
			return $this->job->release($delay);
		}
	}
	public function attempts()
	{
		return $this->job ? $this->job->attempts() : 1;
	}
	public function setJob(JobContract $job)
	{
		$this->job = $job;
		return $this;
	}
}
