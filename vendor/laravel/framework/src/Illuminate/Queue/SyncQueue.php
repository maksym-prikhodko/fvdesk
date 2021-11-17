<?php namespace Illuminate\Queue;
use Illuminate\Queue\Jobs\SyncJob;
use Illuminate\Contracts\Queue\Queue as QueueContract;
class SyncQueue extends Queue implements QueueContract {
	public function push($job, $data = '', $queue = null)
	{
		$this->resolveJob($this->createPayload($job, $data, $queue))->fire();
		return 0;
	}
	public function pushRaw($payload, $queue = null, array $options = array())
	{
	}
	public function later($delay, $job, $data = '', $queue = null)
	{
		return $this->push($job, $data, $queue);
	}
	public function pop($queue = null)
	{
	}
	protected function resolveJob($payload)
	{
		return new SyncJob($this->container, $payload);
	}
}
