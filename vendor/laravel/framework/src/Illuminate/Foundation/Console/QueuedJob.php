<?php namespace Illuminate\Foundation\Console;
use Illuminate\Contracts\Console\Kernel as KernelContract;
class QueuedJob {
	protected $kernel;
	public function __construct(KernelContract $kernel)
	{
		$this->kernel = $kernel;
	}
	public function fire($job, $data)
	{
		call_user_func_array([$this->kernel, 'call'], $data);
		$job->delete();
	}
}
