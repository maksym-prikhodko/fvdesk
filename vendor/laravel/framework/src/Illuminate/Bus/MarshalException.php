<?php namespace Illuminate\Bus;
use RuntimeException;
use ReflectionParameter;
class MarshalException extends RuntimeException {
	public static function whileMapping($command, ReflectionParameter $parameter)
	{
		throw new static("Unable to map parameter [{$parameter->name}] to command [{$command}]");
	}
}
