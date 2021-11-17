<?php namespace Illuminate\Mail;
use Swift_Image;
use Swift_Attachment;
class Message {
	protected $swift;
	public function __construct($swift)
	{
		$this->swift = $swift;
	}
	public function from($address, $name = null)
	{
		$this->swift->setFrom($address, $name);
		return $this;
	}
	public function sender($address, $name = null)
	{
		$this->swift->setSender($address, $name);
		return $this;
	}
	public function returnPath($address)
	{
		$this->swift->setReturnPath($address);
		return $this;
	}
	public function to($address, $name = null)
	{
		return $this->addAddresses($address, $name, 'To');
	}
	public function cc($address, $name = null)
	{
		return $this->addAddresses($address, $name, 'Cc');
	}
	public function bcc($address, $name = null)
	{
		return $this->addAddresses($address, $name, 'Bcc');
	}
	public function replyTo($address, $name = null)
	{
		return $this->addAddresses($address, $name, 'ReplyTo');
	}
	protected function addAddresses($address, $name, $type)
	{
		if (is_array($address))
		{
			$this->swift->{"set{$type}"}($address, $name);
		}
		else
		{
			$this->swift->{"add{$type}"}($address, $name);
		}
		return $this;
	}
	public function subject($subject)
	{
		$this->swift->setSubject($subject);
		return $this;
	}
	public function priority($level)
	{
		$this->swift->setPriority($level);
		return $this;
	}
	public function attach($file, array $options = array())
	{
		$attachment = $this->createAttachmentFromPath($file);
		return $this->prepAttachment($attachment, $options);
	}
	protected function createAttachmentFromPath($file)
	{
		return Swift_Attachment::fromPath($file);
	}
	public function attachData($data, $name, array $options = array())
	{
		$attachment = $this->createAttachmentFromData($data, $name);
		return $this->prepAttachment($attachment, $options);
	}
	protected function createAttachmentFromData($data, $name)
	{
		return Swift_Attachment::newInstance($data, $name);
	}
	public function embed($file)
	{
		return $this->swift->embed(Swift_Image::fromPath($file));
	}
	public function embedData($data, $name, $contentType = null)
	{
		$image = Swift_Image::newInstance($data, $name, $contentType);
		return $this->swift->embed($image);
	}
	protected function prepAttachment($attachment, $options = array())
	{
		if (isset($options['mime']))
		{
			$attachment->setContentType($options['mime']);
		}
		if (isset($options['as']))
		{
			$attachment->setFilename($options['as']);
		}
		$this->swift->attach($attachment);
		return $this;
	}
	public function getSwiftMessage()
	{
		return $this->swift;
	}
	public function __call($method, $parameters)
	{
		$callable = array($this->swift, $method);
		return call_user_func_array($callable, $parameters);
	}
}
