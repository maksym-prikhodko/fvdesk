<?php namespace Illuminate\Bus;
use Closure;
use ArrayAccess;
use ReflectionClass;
use RuntimeException;
use ReflectionParameter;
use InvalidArgumentException;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Queue\Queue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Bus\HandlerResolver;
use Illuminate\Contracts\Queue\ShouldBeQueued;
use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Contracts\Bus\Dispatcher as DispatcherContract;
class Dispatcher implements DispatcherContract, QueueingDispatcher, HandlerResolver {
	protected $container;
	protected $pipeline;
	protected $pipes = [];
	protected $queueResolver;
	protected $mappings = [];
	protected $mapper;
	public function __construct(Container $container, Closure $queueResolver = null)
	{
		$this->container = $container;
		$this->queueResolver = $queueResolver;
		$this->pipeline = new Pipeline($container);
	}
	public function dispatchFromArray($command, array $array)
	{
		return $this->dispatch($this->marshalFromArray($command, $array));
	}
	public function dispatchFrom($command, ArrayAccess $source, array $extras = [])
	{
		return $this->dispatch($this->marshal($command, $source, $extras));
	}
	protected function marshalFromArray($command, array $array)
	{
		return $this->marshal($command, new Collection, $array);
	}
	protected function marshal($command, ArrayAccess $source, array $extras = [])
	{
		$injected = [];
		$reflection = new ReflectionClass($command);
		if ($constructor = $reflection->getConstructor())
		{
			$injected = array_map(function($parameter) use ($command, $source, $extras)
			{
				return $this->getParameterValueForCommand($command, $source, $parameter, $extras);
			}, $constructor->getParameters());
		}
		return $reflection->newInstanceArgs($injected);
	}
	protected function getParameterValueForCommand($command, ArrayAccess $source,
                                                   ReflectionParameter $parameter, array $extras = array())
	{
		if (array_key_exists($parameter->name, $extras))
		{
			return $extras[$parameter->name];
		}
		if (isset($source[$parameter->name]))
		{
			return $source[$parameter->name];
		}
		if ($parameter->isDefaultValueAvailable())
		{
			return $parameter->getDefaultValue();
		}
		MarshalException::whileMapping($command, $parameter);
	}
	public function dispatch($command, Closure $afterResolving = null)
	{
		if ($this->queueResolver && $this->commandShouldBeQueued($command))
		{
			return $this->dispatchToQueue($command);
		}
		else
		{
			return $this->dispatchNow($command, $afterResolving);
		}
	}
	public function dispatchNow($command, Closure $afterResolving = null)
	{
		return $this->pipeline->send($command)->through($this->pipes)->then(function($command) use ($afterResolving)
		{
			if ($command instanceof SelfHandling)
			{
				return $this->container->call([$command, 'handle']);
			}
			$handler = $this->resolveHandler($command);
			if ($afterResolving)
			{
				call_user_func($afterResolving, $handler);
			}
			return call_user_func(
				[$handler, $this->getHandlerMethod($command)], $command
			);
		});
	}
	protected function commandShouldBeQueued($command)
	{
		if ($command instanceof ShouldBeQueued) return true;
		return (new ReflectionClass($this->getHandlerClass($command)))->implementsInterface(
			'Illuminate\Contracts\Queue\ShouldBeQueued'
		);
	}
	public function dispatchToQueue($command)
	{
		$queue = call_user_func($this->queueResolver);
		if ( ! $queue instanceof Queue)
		{
			throw new RuntimeException("Queue resolver did not return a Queue implementation.");
		}
		if (method_exists($command, 'queue'))
		{
			$command->queue($queue, $command);
		}
		else
		{
			$queue->push($command);
		}
	}
	public function resolveHandler($command)
	{
		if ($command instanceof SelfHandling) return $command;
		return $this->container->make($this->getHandlerClass($command));
	}
	public function getHandlerClass($command)
	{
		if ($command instanceof SelfHandling) return get_class($command);
		return $this->inflectSegment($command, 0);
	}
	public function getHandlerMethod($command)
	{
		if ($command instanceof SelfHandling) return 'handle';
		return $this->inflectSegment($command, 1);
	}
	protected function inflectSegment($command, $segment)
	{
		$className = get_class($command);
		if (isset($this->mappings[$className]))
		{
			return $this->getMappingSegment($className, $segment);
		}
		elseif ($this->mapper)
		{
			return $this->getMapperSegment($command, $segment);
		}
		throw new InvalidArgumentException("No handler registered for command [{$className}]");
	}
	protected function getMappingSegment($className, $segment)
	{
		return explode('@', $this->mappings[$className])[$segment];
	}
	protected function getMapperSegment($command, $segment)
	{
		return explode('@', call_user_func($this->mapper, $command))[$segment];
	}
	public function maps(array $commands)
	{
		$this->mappings = array_merge($this->mappings, $commands);
	}
	public function mapUsing(Closure $mapper)
	{
		$this->mapper = $mapper;
	}
	public static function simpleMapping($command, $commandNamespace, $handlerNamespace)
	{
		$command = str_replace($commandNamespace, '', get_class($command));
		return $handlerNamespace.'\\'.trim($command, '\\').'Handler@handle';
	}
	public function pipeThrough(array $pipes)
	{
		$this->pipes = $pipes;
		return $this;
	}
}
