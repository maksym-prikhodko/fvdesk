<?php namespace Illuminate\Console\Scheduling;
use LogicException;
use InvalidArgumentException;
use Illuminate\Contracts\Container\Container;
class CallbackEvent extends Event {
	protected $callback;
	protected $parameters;
	public function __construct($callback, array $parameters = array())
	{
		$this->callback = $callback;
		$this->parameters = $parameters;
		if ( ! is_string($this->callback) && ! is_callable($this->callback))
		{
			throw new InvalidArgumentException(
				"Invalid scheduled callback event. Must be string or callable."
			);
		}
	}
	public function run(Container $container)
	{
		if ($this->description)
		{
			touch($this->mutexPath());
		}
		try {
			$response = $container->call($this->callback, $this->parameters);
		} catch (\Exception $e) {
			$this->removeMutex();
			throw $e;
		}
		$this->removeMutex();
		parent::callAfterCallbacks($container);
		return $response;
	}
	protected function removeMutex()
	{
		if ($this->description)
		{
			@unlink($this->mutexPath());
		}
	}
	public function withoutOverlapping()
	{
		if ( ! isset($this->description))
		{
			throw new LogicException(
				"A scheduled event name is required to prevent overlapping. Use the 'name' method before 'withoutOverlapping'."
			);
		}
		return $this->skip(function()
		{
			return file_exists($this->mutexPath());
		});
	}
	protected function mutexPath()
	{
		return storage_path().'/framework/schedule-'.md5($this->description);
	}
	public function getSummaryForDisplay()
	{
		if (is_string($this->description)) return $this->description;
		return is_string($this->callback) ? $this->callback : 'Closure';
	}
}
