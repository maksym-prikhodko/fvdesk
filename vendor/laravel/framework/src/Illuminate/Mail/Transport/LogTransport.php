<?php namespace Illuminate\Mail\Transport;
use Swift_Transport;
use Swift_Mime_Message;
use Swift_Mime_MimeEntity;
use Psr\Log\LoggerInterface;
use Swift_Events_EventListener;
class LogTransport implements Swift_Transport {
	protected $logger;
	public function __construct(LoggerInterface $logger)
	{
		$this->logger = $logger;
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
		$this->logger->debug($this->getMimeEntityString($message));
	}
	protected function getMimeEntityString(Swift_Mime_MimeEntity $entity)
	{
		$string = (string) $entity->getHeaders().PHP_EOL.$entity->getBody();
		foreach ($entity->getChildren() as $children)
		{
			$string .= PHP_EOL.PHP_EOL.$this->getMimeEntityString($children);
		}
		return $string;
	}
	public function registerPlugin(Swift_Events_EventListener $plugin)
	{
	}
}
