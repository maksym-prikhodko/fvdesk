<?php namespace Illuminate\Queue\Jobs;
use Aws\Sqs\SqsClient;
use Illuminate\Container\Container;
use Illuminate\Contracts\Queue\Job as JobContract;
class SqsJob extends Job implements JobContract {
	protected $sqs;
	protected $job;
	public function __construct(Container $container,
                                SqsClient $sqs,
                                $queue,
                                array $job)
	{
		$this->sqs = $sqs;
		$this->job = $job;
		$this->queue = $queue;
		$this->container = $container;
	}
	public function fire()
	{
		$this->resolveAndFire(json_decode($this->getRawBody(), true));
	}
	public function getRawBody()
	{
		return $this->job['Body'];
	}
	public function delete()
	{
		parent::delete();
		$this->sqs->deleteMessage(array(
			'QueueUrl' => $this->queue, 'ReceiptHandle' => $this->job['ReceiptHandle'],
		));
	}
	public function release($delay = 0)
	{
		parent::release($delay);
		$this->sqs->changeMessageVisibility([
			'QueueUrl' => $this->queue,
			'ReceiptHandle' => $this->job['ReceiptHandle'],
			'VisibilityTimeout' => $delay,
		]);
	}
	public function attempts()
	{
		return (int) $this->job['Attributes']['ApproximateReceiveCount'];
	}
	public function getJobId()
	{
		return $this->job['MessageId'];
	}
	public function getContainer()
	{
		return $this->container;
	}
	public function getSqs()
	{
		return $this->sqs;
	}
	public function getSqsJob()
	{
		return $this->job;
	}
}
