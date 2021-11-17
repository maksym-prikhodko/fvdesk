<?php namespace Illuminate\Foundation;
use Closure;
class EnvironmentDetector {
	public function detect(Closure $callback, $consoleArgs = null)
	{
		if ($consoleArgs)
		{
			return $this->detectConsoleEnvironment($callback, $consoleArgs);
		}
		return $this->detectWebEnvironment($callback);
	}
	protected function detectWebEnvironment(Closure $callback)
	{
		return call_user_func($callback);
	}
	protected function detectConsoleEnvironment(Closure $callback, array $args)
	{
		if ( ! is_null($value = $this->getEnvironmentArgument($args)))
		{
			return head(array_slice(explode('=', $value), 1));
		}
		return $this->detectWebEnvironment($callback);
	}
	protected function getEnvironmentArgument(array $args)
	{
		return array_first($args, function($k, $v)
		{
			return starts_with($v, '--env');
		});
	}
}
