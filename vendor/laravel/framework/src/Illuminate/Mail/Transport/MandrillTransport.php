<?php namespace Illuminate\Mail\Transport;
use Swift_Transport;
use GuzzleHttp\Client;
use Swift_Mime_Message;
use Swift_Events_EventListener;
class MandrillTransport implements Swift_Transport {
	protected $key;
	public function __construct($key)
	{
		$this->key = $key;
	}
	public function isStarted()
	{
		return true;
	}
	public function start()
	{
		return true;
	}
	public function stop()
	{
		return true;
	}
	public function send(Swift_Mime_Message $message, &$failedRecipients = null)
	{
		$client = $this->getHttpClient();
		return $client->post('https:
			'body' => [
				'key' => $this->key,
				'to' => $this->getToAddresses($message),
				'raw_message' => (string) $message,
				'async' => false,
			],
		]);
	}
	protected function getToAddresses(Swift_Mime_Message $message)
	{
		$to = [];
		if ($message->getTo())
		{
			$to = array_merge($to, array_keys($message->getTo()));
		}
		if ($message->getCc())
		{
			$to = array_merge($to, array_keys($message->getCc()));
		}
		if ($message->getBcc())
		{
			$to = array_merge($to, array_keys($message->getBcc()));
		}
		return $to;
	}
	public function registerPlugin(Swift_Events_EventListener $plugin)
	{
	}
	protected function getHttpClient()
	{
		return new Client;
	}
	public function getKey()
	{
		return $this->key;
	}
	public function setKey($key)
	{
		return $this->key = $key;
	}
}
