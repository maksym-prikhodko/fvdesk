<?php namespace Illuminate\Queue;
use Closure;
use DateTime;
use RuntimeException;
use SuperClosure\Serializer;
use Illuminate\Container\Container;
use Illuminate\Contracts\Queue\QueueableEntity;
use Illuminate\Contracts\Encryption\Encrypter as EncrypterContract;
abstract class Queue {
	protected $container;
	public function pushOn($queue, $job, $data = '')
	{
		return $this->push($job, $data, $queue);
	}
	public function laterOn($queue, $delay, $job, $data = '')
	{
		return $this->later($delay, $job, $data, $queue);
	}
	public function marshal()
	{
		throw new RuntimeException("Push queues only supported by Iron.");
	}
	public function bulk($jobs, $data = '', $queue = null)
	{
		foreach ((array) $jobs as $job)
		{
			$this->push($job, $data, $queue);
		}
	}
	protected function createPayload($job, $data = '', $queue = null)
	{
		if ($job instanceof Closure)
		{
			return json_encode($this->createClosurePayload($job, $data));
		}
		elseif (is_object($job))
		{
			return json_encode([
				'job' => 'Illuminate\Queue\CallQueuedHandler@call',
				'data' => ['command' => serialize(clone $job)],
			]);
		}
		return json_encode($this->createPlainPayload($job, $data));
	}
	protected function createPlainPayload($job, $data)
	{
		return ['job' => $job, 'data' => $this->prepareQueueableEntities($data)];
	}
	protected function prepareQueueableEntities($data)
	{
		if ($data instanceof QueueableEntity)
		{
			return $this->prepareQueueableEntity($data);
		}
		if (is_array($data))
		{
			array_walk($data, function(&$d) { $d = $this->prepareQueueableEntity($d); });
		}
		return $data;
	}
	protected function prepareQueueableEntity($value)
	{
		if ($value instanceof QueueableEntity)
		{
			return '::entity::|'.get_class($value).'|'.$value->getQueueableId();
		}
		return $value;
	}
	protected function createClosurePayload($job, $data)
	{
		$closure = $this->crypt->encrypt((new Serializer)->serialize($job));
		return ['job' => 'IlluminateQueueClosure', 'data' => compact('closure')];
	}
	protected function setMeta($payload, $key, $value)
	{
		$payload = json_decode($payload, true);
		return json_encode(array_set($payload, $key, $value));
	}
	protected function getSeconds($delay)
	{
		if ($delay instanceof DateTime)
		{
			return max(0, $delay->getTimestamp() - $this->getTime());
		}
		return (int) $delay;
	}
	protected function getTime()
	{
		return time();
	}
	public function setContainer(Container $container)
	{
		$this->container = $container;
	}
	public function setEncrypter(EncrypterContract $crypt)
	{
		$this->crypt = $crypt;
	}
}
