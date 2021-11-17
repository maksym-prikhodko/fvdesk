<?php namespace Illuminate\Support;
use Countable;
use JsonSerializable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\MessageProvider;
use Illuminate\Contracts\Support\MessageBag as MessageBagContract;
class MessageBag implements Arrayable, Countable, Jsonable, JsonSerializable, MessageBagContract, MessageProvider  {
	protected $messages = array();
	protected $format = ':message';
	public function __construct(array $messages = array())
	{
		foreach ($messages as $key => $value)
		{
			$this->messages[$key] = (array) $value;
		}
	}
	public function keys()
	{
		return array_keys($this->messages);
	}
	public function add($key, $message)
	{
		if ($this->isUnique($key, $message))
		{
			$this->messages[$key][] = $message;
		}
		return $this;
	}
	public function merge($messages)
	{
		if ($messages instanceof MessageProvider)
		{
			$messages = $messages->getMessageBag()->getMessages();
		}
		$this->messages = array_merge_recursive($this->messages, $messages);
		return $this;
	}
	protected function isUnique($key, $message)
	{
		$messages = (array) $this->messages;
		return ! isset($messages[$key]) || ! in_array($message, $messages[$key]);
	}
	public function has($key = null)
	{
		return $this->first($key) !== '';
	}
	public function first($key = null, $format = null)
	{
		$messages = is_null($key) ? $this->all($format) : $this->get($key, $format);
		return count($messages) > 0 ? $messages[0] : '';
	}
	public function get($key, $format = null)
	{
		if (array_key_exists($key, $this->messages))
		{
			return $this->transform($this->messages[$key], $this->checkFormat($format), $key);
		}
		return array();
	}
	public function all($format = null)
	{
		$format = $this->checkFormat($format);
		$all = array();
		foreach ($this->messages as $key => $messages)
		{
			$all = array_merge($all, $this->transform($messages, $format, $key));
		}
		return $all;
	}
	protected function transform($messages, $format, $messageKey)
	{
		$messages = (array) $messages;
		$replace = array(':message', ':key');
		foreach ($messages as &$message)
		{
			$message = str_replace($replace, array($message, $messageKey), $format);
		}
		return $messages;
	}
	protected function checkFormat($format)
	{
		return $format ?: $this->format;
	}
	public function getMessages()
	{
		return $this->messages;
	}
	public function getMessageBag()
	{
		return $this;
	}
	public function getFormat()
	{
		return $this->format;
	}
	public function setFormat($format = ':message')
	{
		$this->format = $format;
		return $this;
	}
	public function isEmpty()
	{
		return ! $this->any();
	}
	public function any()
	{
		return $this->count() > 0;
	}
	public function count()
	{
		return count($this->messages, COUNT_RECURSIVE) - count($this->messages);
	}
	public function toArray()
	{
		return $this->getMessages();
	}
	public function jsonSerialize()
	{
		return $this->toArray();
	}
	public function toJson($options = 0)
	{
		return json_encode($this->toArray(), $options);
	}
	public function __toString()
	{
		return $this->toJson();
	}
}
