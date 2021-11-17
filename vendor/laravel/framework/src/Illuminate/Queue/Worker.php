<?php namespace Illuminate\Queue;
use Exception;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Queue\Failed\FailedJobProviderInterface;
use Illuminate\Contracts\Cache\Repository as CacheContract;
use Illuminate\Contracts\Debug\ExceptionHandler;
class Worker {
	protected $manager;
	protected $failer;
	protected $events;
	protected $cache;
	protected $exceptions;
	public function __construct(QueueManager $manager,
                                FailedJobProviderInterface $failer = null,
                                Dispatcher $events = null)
	{
		$this->failer = $failer;
		$this->events = $events;
		$this->manager = $manager;
	}
	public function daemon($connectionName, $queue = null, $delay = 0, $memory = 128, $sleep = 3, $maxTries = 0)
	{
		$lastRestart = $this->getTimestampOfLastQueueRestart();
		while (true)
		{
			if ($this->daemonShouldRun())
			{
				$this->runNextJobForDaemon(
					$connectionName, $queue, $delay, $sleep, $maxTries
				);
			}
			else
			{
				$this->sleep($sleep);
			}
			if ($this->memoryExceeded($memory) || $this->queueShouldRestart($lastRestart))
			{
				$this->stop();
			}
		}
	}
	protected function runNextJobForDaemon($connectionName, $queue, $delay, $sleep, $maxTries)
	{
		try
		{
			$this->pop($connectionName, $queue, $delay, $sleep, $maxTries);
		}
		catch (Exception $e)
		{
			if ($this->exceptions) $this->exceptions->report($e);
		}
	}
	protected function daemonShouldRun()
	{
		if ($this->manager->isDownForMaintenance())
		{
			return false;
		}
		return $this->events->until('illuminate.queue.looping') !== false;
	}
	public function pop($connectionName, $queue = null, $delay = 0, $sleep = 3, $maxTries = 0)
	{
		$connection = $this->manager->connection($connectionName);
		$job = $this->getNextJob($connection, $queue);
		if ( ! is_null($job))
		{
			return $this->process(
				$this->manager->getName($connectionName), $job, $maxTries, $delay
			);
		}
		$this->sleep($sleep);
		return ['job' => null, 'failed' => false];
	}
	protected function getNextJob($connection, $queue)
	{
		if (is_null($queue)) return $connection->pop();
		foreach (explode(',', $queue) as $queue)
		{
			if ( ! is_null($job = $connection->pop($queue))) return $job;
		}
	}
	public function process($connection, Job $job, $maxTries = 0, $delay = 0)
	{
		if ($maxTries > 0 && $job->attempts() > $maxTries)
		{
			return $this->logFailedJob($connection, $job);
		}
		try
		{
			$job->fire();
			return ['job' => $job, 'failed' => false];
		}
		catch (Exception $e)
		{
			if ( ! $job->isDeleted()) $job->release($delay);
			throw $e;
		}
	}
	protected function logFailedJob($connection, Job $job)
	{
		if ($this->failer)
		{
			$this->failer->log($connection, $job->getQueue(), $job->getRawBody());
			$job->delete();
			$job->failed();
			$this->raiseFailedJobEvent($connection, $job);
		}
		return ['job' => $job, 'failed' => true];
	}
	protected function raiseFailedJobEvent($connection, Job $job)
	{
		if ($this->events)
		{
			$data = json_decode($job->getRawBody(), true);
			$this->events->fire('illuminate.queue.failed', array($connection, $job, $data));
		}
	}
	public function memoryExceeded($memoryLimit)
	{
		return (memory_get_usage() / 1024 / 1024) >= $memoryLimit;
	}
	public function stop()
	{
		$this->events->fire('illuminate.queue.stopping');
		die;
	}
	public function sleep($seconds)
	{
		sleep($seconds);
	}
	protected function getTimestampOfLastQueueRestart()
	{
		if ($this->cache)
		{
			return $this->cache->get('illuminate:queue:restart');
		}
	}
	protected function queueShouldRestart($lastRestart)
	{
		return $this->getTimestampOfLastQueueRestart() != $lastRestart;
	}
	public function setDaemonExceptionHandler(ExceptionHandler $handler)
	{
		$this->exceptions = $handler;
	}
	public function setCache(CacheContract $cache)
	{
		$this->cache = $cache;
	}
	public function getManager()
	{
		return $this->manager;
	}
	public function setManager(QueueManager $manager)
	{
		$this->manager = $manager;
	}
}
