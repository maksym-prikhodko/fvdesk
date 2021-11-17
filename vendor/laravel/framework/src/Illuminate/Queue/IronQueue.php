<?php namespace Illuminate\Queue;
use IronMQ;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Queue\Jobs\IronJob;
use Illuminate\Contracts\Queue\Queue as QueueContract;
class IronQueue extends Queue implements QueueContract {
	protected $iron;
	protected $request;
	protected $default;
	protected $shouldEncrypt;
	public function __construct(IronMQ $iron, Request $request, $default, $shouldEncrypt = false)
	{
		$this->iron = $iron;
		$this->request = $request;
		$this->default = $default;
		$this->shouldEncrypt = $shouldEncrypt;
	}
	public function push($job, $data = '', $queue = null)
	{
		return $this->pushRaw($this->createPayload($job, $data, $queue), $queue);
	}
	public function pushRaw($payload, $queue = null, array $options = array())
	{
		if ($this->shouldEncrypt) $payload = $this->crypt->encrypt($payload);
		return $this->iron->postMessage($this->getQueue($queue), $payload, $options)->id;
	}
	public function recreate($payload, $queue = null, $delay)
	{
		$options = array('delay' => $this->getSeconds($delay));
		return $this->pushRaw($payload, $queue, $options);
	}
	public function later($delay, $job, $data = '', $queue = null)
	{
		$delay = $this->getSeconds($delay);
		$payload = $this->createPayload($job, $data, $queue);
		return $this->pushRaw($payload, $queue, compact('delay'));
	}
	public function pop($queue = null)
	{
		$queue = $this->getQueue($queue);
		$job = $this->iron->getMessage($queue);
		if ( ! is_null($job))
		{
			$job->body = $this->parseJobBody($job->body);
			return new IronJob($this->container, $this, $job);
		}
	}
	public function deleteMessage($queue, $id)
	{
		$this->iron->deleteMessage($queue, $id);
	}
	public function marshal()
	{
		$this->createPushedIronJob($this->marshalPushedJob())->fire();
		return new Response('OK');
	}
	protected function marshalPushedJob()
	{
		$r = $this->request;
		$body = $this->parseJobBody($r->getContent());
		return (object) array(
			'id' => $r->header('iron-message-id'), 'body' => $body, 'pushed' => true,
		);
	}
	protected function createPushedIronJob($job)
	{
		return new IronJob($this->container, $this, $job, true);
	}
	protected function createPayload($job, $data = '', $queue = null)
	{
		$payload = $this->setMeta(parent::createPayload($job, $data), 'attempts', 1);
		return $this->setMeta($payload, 'queue', $this->getQueue($queue));
	}
	protected function parseJobBody($body)
	{
		return $this->shouldEncrypt ? $this->crypt->decrypt($body) : $body;
	}
	public function getQueue($queue)
	{
		return $queue ?: $this->default;
	}
	public function getIron()
	{
		return $this->iron;
	}
	public function getRequest()
	{
		return $this->request;
	}
	public function setRequest(Request $request)
	{
		$this->request = $request;
	}
}
