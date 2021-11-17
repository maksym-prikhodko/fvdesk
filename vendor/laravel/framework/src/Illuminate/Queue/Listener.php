<?php namespace Illuminate\Queue;
use Closure;
use Symfony\Component\Process\Process;
class Listener {
	protected $commandPath;
	protected $environment;
	protected $sleep = 3;
	protected $maxTries = 0;
	protected $workerCommand;
	protected $outputHandler;
	public function __construct($commandPath)
	{
		$this->commandPath = $commandPath;
		$this->workerCommand =  '"'.PHP_BINARY.'" artisan queue:work %s --queue="%s" --delay=%s --memory=%s --sleep=%s --tries=%s';
	}
	public function listen($connection, $queue, $delay, $memory, $timeout = 60)
	{
		$process = $this->makeProcess($connection, $queue, $delay, $memory, $timeout);
		while (true)
		{
			$this->runProcess($process, $memory);
		}
	}
	public function runProcess(Process $process, $memory)
	{
		$process->run(function($type, $line)
		{
			$this->handleWorkerOutput($type, $line);
		});
		if ($this->memoryExceeded($memory))
		{
			$this->stop();
		}
	}
	public function makeProcess($connection, $queue, $delay, $memory, $timeout)
	{
		$string = $this->workerCommand;
		if (isset($this->environment))
		{
			$string .= ' --env='.$this->environment;
		}
		$command = sprintf(
			$string, $connection, $queue, $delay,
			$memory, $this->sleep, $this->maxTries
		);
		return new Process($command, $this->commandPath, null, null, $timeout);
	}
	protected function handleWorkerOutput($type, $line)
	{
		if (isset($this->outputHandler))
		{
			call_user_func($this->outputHandler, $type, $line);
		}
	}
	public function memoryExceeded($memoryLimit)
	{
		return (memory_get_usage() / 1024 / 1024) >= $memoryLimit;
	}
	public function stop()
	{
		die;
	}
	public function setOutputHandler(Closure $outputHandler)
	{
		$this->outputHandler = $outputHandler;
	}
	public function getEnvironment()
	{
		return $this->environment;
	}
	public function setEnvironment($environment)
	{
		$this->environment = $environment;
	}
	public function getSleep()
	{
		return $this->sleep;
	}
	public function setSleep($sleep)
	{
		$this->sleep = $sleep;
	}
	public function setMaxTries($tries)
	{
		$this->maxTries = $tries;
	}
}
