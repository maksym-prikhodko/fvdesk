<?php namespace Illuminate\Queue;
use Aws\Sqs\SqsClient;
use Illuminate\Queue\Jobs\SqsJob;
use Illuminate\Contracts\Queue\Queue as QueueContract;
class SqsQueue extends Queue implements QueueContract {
	protected $sqs;
	protected $default;
	public function __construct(SqsClient $sqs, $default)
	{
		$this->sqs = $sqs;
		$this->default = $default;
	}
	public function push($job, $data = '', $queue = null)
	{
		return $this->pushRaw($this->createPayload($job, $data), $queue);
	}
	public function pushRaw($payload, $queue = null, array $options = array())
	{
		$response = $this->sqs->sendMessage(array('QueueUrl' => $this->getQueue($queue), 'MessageBody' => $payload));
		return $response->get('MessageId');
	}
	public function later($delay, $job, $data = '', $queue = null)
	{
		$payload = $this->createPayload($job, $data);
		$delay = $this->getSeconds($delay);
		return $this->sqs->sendMessage(array(
			'QueueUrl' => $this->getQueue($queue), 'MessageBody' => $payload, 'DelaySeconds' => $delay,
		))->get('MessageId');
	}
	public function pop($queue = null)
	{
		$queue = $this->getQueue($queue);
		$response = $this->sqs->receiveMessage(
			array('QueueUrl' => $queue, 'AttributeNames' => array('ApproximateReceiveCount'))
		);
		if (count($response['Messages']) > 0)
		{
			return new SqsJob($this->container, $this->sqs, $queue, $response['Messages'][0]);
		}
	}
	public function getQueue($queue)
	{
		return $queue ?: $this->default;
	}
	public function getSqs()
	{
		return $this->sqs;
	}
}
