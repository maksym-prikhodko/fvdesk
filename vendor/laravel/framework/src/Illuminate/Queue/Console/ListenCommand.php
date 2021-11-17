<?php namespace Illuminate\Queue\Console;
use Illuminate\Queue\Listener;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
class ListenCommand extends Command {
	protected $name = 'queue:listen';
	protected $description = 'Listen to a given queue';
	protected $listener;
	public function __construct(Listener $listener)
	{
		parent::__construct();
		$this->listener = $listener;
	}
	public function fire()
	{
		$this->setListenerOptions();
		$delay = $this->input->getOption('delay');
		$memory = $this->input->getOption('memory');
		$connection = $this->input->getArgument('connection');
		$timeout = $this->input->getOption('timeout');
		$queue = $this->getQueue($connection);
		$this->listener->listen(
			$connection, $queue, $delay, $memory, $timeout
		);
	}
	protected function getQueue($connection)
	{
		if (is_null($connection))
		{
			$connection = $this->laravel['config']['queue.default'];
		}
		$queue = $this->laravel['config']->get("queue.connections.{$connection}.queue", 'default');
		return $this->input->getOption('queue') ?: $queue;
	}
	protected function setListenerOptions()
	{
		$this->listener->setEnvironment($this->laravel->environment());
		$this->listener->setSleep($this->option('sleep'));
		$this->listener->setMaxTries($this->option('tries'));
		$this->listener->setOutputHandler(function($type, $line)
		{
			$this->output->write($line);
		});
	}
	protected function getArguments()
	{
		return array(
			array('connection', InputArgument::OPTIONAL, 'The name of connection'),
		);
	}
	protected function getOptions()
	{
		return array(
			array('queue', null, InputOption::VALUE_OPTIONAL, 'The queue to listen on', null),
			array('delay', null, InputOption::VALUE_OPTIONAL, 'Amount of time to delay failed jobs', 0),
			array('memory', null, InputOption::VALUE_OPTIONAL, 'The memory limit in megabytes', 128),
			array('timeout', null, InputOption::VALUE_OPTIONAL, 'Seconds a job may run before timing out', 60),
			array('sleep', null, InputOption::VALUE_OPTIONAL, 'Seconds to wait before checking queue for jobs', 3),
			array('tries', null, InputOption::VALUE_OPTIONAL, 'Number of times to attempt a job before logging it failed', 0),
		);
	}
}
