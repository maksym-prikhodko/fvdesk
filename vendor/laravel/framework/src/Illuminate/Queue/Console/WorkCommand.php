<?php namespace Illuminate\Queue\Console;
use Illuminate\Queue\Worker;
use Illuminate\Console\Command;
use Illuminate\Contracts\Queue\Job;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
class WorkCommand extends Command {
	protected $name = 'queue:work';
	protected $description = 'Process the next job on a queue';
	protected $worker;
	public function __construct(Worker $worker)
	{
		parent::__construct();
		$this->worker = $worker;
	}
	public function fire()
	{
		if ($this->downForMaintenance() && ! $this->option('daemon')) return;
		$queue = $this->option('queue');
		$delay = $this->option('delay');
		$memory = $this->option('memory');
		$connection = $this->argument('connection');
		$response = $this->runWorker(
			$connection, $queue, $delay, $memory, $this->option('daemon')
		);
		if ( ! is_null($response['job']))
		{
			$this->writeOutput($response['job'], $response['failed']);
		}
	}
	protected function runWorker($connection, $queue, $delay, $memory, $daemon = false)
	{
		if ($daemon)
		{
			$this->worker->setCache($this->laravel['cache']->driver());
			$this->worker->setDaemonExceptionHandler(
				$this->laravel['Illuminate\Contracts\Debug\ExceptionHandler']
			);
			return $this->worker->daemon(
				$connection, $queue, $delay, $memory,
				$this->option('sleep'), $this->option('tries')
			);
		}
		return $this->worker->pop(
			$connection, $queue, $delay,
			$this->option('sleep'), $this->option('tries')
		);
	}
	protected function writeOutput(Job $job, $failed)
	{
		if ($failed)
		{
			$this->output->writeln('<error>Failed:</error> '.$job->getName());
		}
		else
		{
			$this->output->writeln('<info>Processed:</info> '.$job->getName());
		}
	}
	protected function downForMaintenance()
	{
		if ($this->option('force')) return false;
		return $this->laravel->isDownForMaintenance();
	}
	protected function getArguments()
	{
		return array(
			array('connection', InputArgument::OPTIONAL, 'The name of connection', null),
		);
	}
	protected function getOptions()
	{
		return array(
			array('queue', null, InputOption::VALUE_OPTIONAL, 'The queue to listen on'),
			array('daemon', null, InputOption::VALUE_NONE, 'Run the worker in daemon mode'),
			array('delay', null, InputOption::VALUE_OPTIONAL, 'Amount of time to delay failed jobs', 0),
			array('force', null, InputOption::VALUE_NONE, 'Force the worker to run even in maintenance mode'),
			array('memory', null, InputOption::VALUE_OPTIONAL, 'The memory limit in megabytes', 128),
			array('sleep', null, InputOption::VALUE_OPTIONAL, 'Number of seconds to sleep when no job is available', 3),
			array('tries', null, InputOption::VALUE_OPTIONAL, 'Number of times to attempt a job before logging it failed', 0),
		);
	}
}
