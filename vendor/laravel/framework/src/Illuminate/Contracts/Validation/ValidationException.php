<?php namespace Illuminate\Contracts\Validation;
use RuntimeException;
use Illuminate\Contracts\Support\MessageProvider;
class ValidationException extends RuntimeException {
	protected $provider;
	public function __construct(MessageProvider $provider)
	{
		$this->provider = $provider;
	}
	public function errors()
	{
		return $this->provider->getMessageBag();
	}
	public function getMessageProvider()
	{
		return $this->provider;
	}
}
