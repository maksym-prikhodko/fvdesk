<?php namespace Illuminate\Mail;
use Closure;
use Swift_Mailer;
use Swift_Message;
use SuperClosure\Serializer;
use Psr\Log\LoggerInterface;
use InvalidArgumentException;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Queue\Queue as QueueContract;
use Illuminate\Contracts\Mail\Mailer as MailerContract;
use Illuminate\Contracts\Mail\MailQueue as MailQueueContract;
class Mailer implements MailerContract, MailQueueContract {
	protected $views;
	protected $swift;
	protected $events;
	protected $from;
	protected $logger;
	protected $container;
	protected $queue;
	protected $pretending = false;
	protected $failedRecipients = array();
	protected $parsedViews = array();
	public function __construct(Factory $views, Swift_Mailer $swift, Dispatcher $events = null)
	{
		$this->views = $views;
		$this->swift = $swift;
		$this->events = $events;
	}
	public function alwaysFrom($address, $name = null)
	{
		$this->from = compact('address', 'name');
	}
	public function raw($text, $callback)
	{
		return $this->send(array('raw' => $text), [], $callback);
	}
	public function plain($view, array $data, $callback)
	{
		return $this->send(array('text' => $view), $data, $callback);
	}
	public function send($view, array $data, $callback)
	{
		list($view, $plain, $raw) = $this->parseView($view);
		$data['message'] = $message = $this->createMessage();
		$this->callMessageBuilder($callback, $message);
		$this->addContent($message, $view, $plain, $raw, $data);
		$message = $message->getSwiftMessage();
		return $this->sendSwiftMessage($message);
	}
	public function queue($view, array $data, $callback, $queue = null)
	{
		$callback = $this->buildQueueCallable($callback);
		return $this->queue->push('mailer@handleQueuedMessage', compact('view', 'data', 'callback'), $queue);
	}
	public function queueOn($queue, $view, array $data, $callback)
	{
		return $this->queue($view, $data, $callback, $queue);
	}
	public function later($delay, $view, array $data, $callback, $queue = null)
	{
		$callback = $this->buildQueueCallable($callback);
		return $this->queue->later($delay, 'mailer@handleQueuedMessage', compact('view', 'data', 'callback'), $queue);
	}
	public function laterOn($queue, $delay, $view, array $data, $callback)
	{
		return $this->later($delay, $view, $data, $callback, $queue);
	}
	protected function buildQueueCallable($callback)
	{
		if ( ! $callback instanceof Closure) return $callback;
		return (new Serializer)->serialize($callback);
	}
	public function handleQueuedMessage($job, $data)
	{
		$this->send($data['view'], $data['data'], $this->getQueuedCallable($data));
		$job->delete();
	}
	protected function getQueuedCallable(array $data)
	{
		if (str_contains($data['callback'], 'SerializableClosure'))
		{
			return unserialize($data['callback'])->getClosure();
		}
		return $data['callback'];
	}
	protected function addContent($message, $view, $plain, $raw, $data)
	{
		if (isset($view))
		{
			$message->setBody($this->getView($view, $data), 'text/html');
		}
		if (isset($plain))
		{
			$message->addPart($this->getView($plain, $data), 'text/plain');
		}
		if (isset($raw))
		{
			$message->addPart($raw, 'text/plain');
		}
	}
	protected function parseView($view)
	{
		if (is_string($view)) return [$view, null, null];
		if (is_array($view) && isset($view[0]))
		{
			return [$view[0], $view[1], null];
		}
		elseif (is_array($view))
		{
			return [
				array_get($view, 'html'),
				array_get($view, 'text'),
				array_get($view, 'raw'),
			];
		}
		throw new InvalidArgumentException("Invalid view.");
	}
	protected function sendSwiftMessage($message)
	{
		if ($this->events)
		{
			$this->events->fire('mailer.sending', array($message));
		}
		if ( ! $this->pretending)
		{
			return $this->swift->send($message, $this->failedRecipients);
		}
		elseif (isset($this->logger))
		{
			$this->logMessage($message);
		}
	}
	protected function logMessage($message)
	{
		$emails = implode(', ', array_keys((array) $message->getTo()));
		$this->logger->info("Pretending to mail message to: {$emails}");
	}
	protected function callMessageBuilder($callback, $message)
	{
		if ($callback instanceof Closure)
		{
			return call_user_func($callback, $message);
		}
		elseif (is_string($callback))
		{
			return $this->container->make($callback)->mail($message);
		}
		throw new InvalidArgumentException("Callback is not valid.");
	}
	protected function createMessage()
	{
		$message = new Message(new Swift_Message);
		if (isset($this->from['address']))
		{
			$message->from($this->from['address'], $this->from['name']);
		}
		return $message;
	}
	protected function getView($view, $data)
	{
		return $this->views->make($view, $data)->render();
	}
	public function pretend($value = true)
	{
		$this->pretending = $value;
	}
	public function isPretending()
	{
		return $this->pretending;
	}
	public function getViewFactory()
	{
		return $this->views;
	}
	public function getSwiftMailer()
	{
		return $this->swift;
	}
	public function failures()
	{
		return $this->failedRecipients;
	}
	public function setSwiftMailer($swift)
	{
		$this->swift = $swift;
	}
	public function setLogger(LoggerInterface $logger)
	{
		$this->logger = $logger;
		return $this;
	}
	public function setQueue(QueueContract $queue)
	{
		$this->queue = $queue;
		return $this;
	}
	public function setContainer(Container $container)
	{
		$this->container = $container;
	}
}
