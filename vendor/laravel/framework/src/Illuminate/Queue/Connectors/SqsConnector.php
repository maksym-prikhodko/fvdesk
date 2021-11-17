<?php namespace Illuminate\Queue\Connectors;
use Aws\Sqs\SqsClient;
use Illuminate\Queue\SqsQueue;
class SqsConnector implements ConnectorInterface {
	public function connect(array $config)
	{
		$sqs = SqsClient::factory($config);
		return new SqsQueue($sqs, $config['queue']);
	}
}
