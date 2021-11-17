<?php namespace Illuminate\Mail\Transport;
use Swift_Transport;
use GuzzleHttp\Client;
use Swift_Mime_Message;
use GuzzleHttp\Post\PostFile;
use Swift_Events_EventListener;
class MailgunTransport implements Swift_Transport {
	protected $key;
	protected $domain;
	protected $url;
	public function __construct($key, $domain)
	{
		$this->key = $key;
		$this->setDomain($domain);
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
		return $client->post($this->url, ['auth' => ['api', $this->key],
			'body' => [
				'to' => $this->getTo($message),
				'message' => new PostFile('message', (string) $message),
			],
		]);
	}
	public function registerPlugin(Swift_Events_EventListener $plugin)
	{
	}
	protected function getTo(Swift_Mime_Message $message)
	{
		$formatted = [];
		$contacts = array_merge(
			(array) $message->getTo(), (array) $message->getCc(), (array) $message->getBcc()
		);
		foreach ($contacts as $address => $display)
		{
			$formatted[] = $display ? $display." <$address>" : $address;
		}
		return implode(',', $formatted);
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
	public function getDomain()
	{
		return $this->domain;
	}
	public function setDomain($domain)
	{
		$this->url = 'https:
		return $this->domain = $domain;
	}
}
