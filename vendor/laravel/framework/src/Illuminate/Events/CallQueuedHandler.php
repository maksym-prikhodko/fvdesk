<?php namespace Illuminate\Events;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Contracts\Container\Container;
class CallQueuedHandler {
	protected $container;
	public function __construct(Container $container)
	{
		$this->container = $container;
	}
	public function call(Job $job, array $data)
	{
		$handler = $this->setJobInstanceIfNecessary(
			$job, $this->container->make($data['class'])
		);
		call_user_func_array(
			[$handler, $data['method']], unserialize($data['data'])
		);
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
		$handler = $this->container->make($data['class']);
		if (method_exists($handler, 'failed'))
		{
			call_user_func_array([$handler, 'failed'], unserialize($data));
		}
	}
}
