<?php namespace Illuminate\Pipeline;
use Closure;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Pipeline\Pipeline as PipelineContract;
class Pipeline implements PipelineContract {
	protected $container;
	protected $passable;
	protected $pipes = array();
	protected $method = 'handle';
	public function __construct(Container $container)
	{
		$this->container = $container;
	}
	public function send($passable)
	{
		$this->passable = $passable;
		return $this;
	}
	public function through($pipes)
	{
		$this->pipes = is_array($pipes) ? $pipes : func_get_args();
		return $this;
	}
	public function via($method)
	{
		$this->method = $method;
		return $this;
	}
	public function then(Closure $destination)
	{
		$firstSlice = $this->getInitialSlice($destination);
		$pipes = array_reverse($this->pipes);
		return call_user_func(
			array_reduce($pipes, $this->getSlice(), $firstSlice), $this->passable
		);
	}
	protected function getSlice()
	{
		return function($stack, $pipe)
		{
			return function($passable) use ($stack, $pipe)
			{
				if ($pipe instanceof Closure)
				{
					return call_user_func($pipe, $passable, $stack);
				}
				else
				{
					return $this->container->make($pipe)
							->{$this->method}($passable, $stack);
				}
			};
		};
	}
	protected function getInitialSlice(Closure $destination)
	{
		return function($passable) use ($destination)
		{
			return call_user_func($destination, $passable);
		};
	}
}
