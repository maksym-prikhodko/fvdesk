<?php namespace Illuminate\Mail\Transport;
use Swift_Transport;
use Aws\Ses\SesClient;
use Swift_Mime_Message;
use Swift_Events_EventListener;
class SesTransport implements Swift_Transport {
	protected $ses;
	public function __construct(SesClient $ses)
	{
		$this->ses = $ses;
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
		return $this->ses->sendRawEmail([
			'Source' => $message->getSender(),
			'Destinations' => $this->getTo($message),
			'RawMessage' => [
				'Data' => base64_encode((string) $message),
			],
		]);
	}
	public function registerPlugin(Swift_Events_EventListener $plugin)
	{
	}
	protected function getTo(Swift_Mime_Message $message)
	{
		$destinations = [];
		$contacts = array_merge(
			(array) $message->getTo(), (array) $message->getCc(), (array) $message->getBcc()
		);
		foreach ($contacts as $address => $display)
		{
			$destinations[] = $address;
		}
		return $destinations;
	}
}
