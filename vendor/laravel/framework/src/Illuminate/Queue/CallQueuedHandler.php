<?php namespace Illuminate\Queue;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Contracts\Bus\Dispatcher;
class CallQueuedHandler {
	protected $dispatcher;
	public function __construct(Dispatcher $dispatcher)
	{
		$this->dispatcher = $dispatcher;
	}
	public function call(Job $job, array $data)
	{
		$command = $this->setJobInstanceIfNecessary(
			$job, unserialize($data['command'])
		);
		$this->dispatcher->dispatchNow($command, function($handler) use ($job)
		{
			$this->setJobInstanceIfNecessary($job, $handler);
		});
		if ( ! $job->isDeletedOrReleased())
		{
			$job->delete();
		}
	}
	protected function setJobInstanceIfNecessary(Job $job, $instance)
	{
		if (in_array('Illuminate\Queue\InteractsWithQueue', class_uses_recursive(get_class($instance))))
		{
			$instance->setJob($job);
		}
		return $instance;
	}
	public function failed(array $data)
	{
		$handler = $this->dispatcher->resolveHandler($command = unserialize($data['command']));
		if (method_exists($handler, 'failed'))
		{
			call_user_func([$handler, 'failed'], $command);
		}
	}
}
