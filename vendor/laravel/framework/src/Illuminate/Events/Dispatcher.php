<?php namespace Illuminate\Events;
use Exception;
use ReflectionClass;
use Illuminate\Container\Container;
use Illuminate\Contracts\Events\Dispatcher as DispatcherContract;
use Illuminate\Contracts\Container\Container as ContainerContract;
class Dispatcher implements DispatcherContract {
	protected $container;
	protected $listeners = array();
	protected $wildcards = array();
	protected $sorted = array();
	protected $firing = array();
	protected $queueResolver;
	public function __construct(ContainerContract $container = null)
	{
		$this->container = $container ?: new Container;
	}
	public function listen($events, $listener, $priority = 0)
	{
		foreach ((array) $events as $event)
		{
			if (str_contains($event, '*'))
			{
				$this->setupWildcardListen($event, $listener);
			}
			else
			{
				$this->listeners[$event][$priority][] = $this->makeListener($listener);
				unset($this->sorted[$event]);
			}
		}
	}
	protected function setupWildcardListen($event, $listener)
	{
		$this->wildcards[$event][] = $this->makeListener($listener);
	}
	public function hasListeners($eventName)
	{
		return isset($this->listeners[$eventName]);
	}
	public function push($event, $payload = array())
	{
		$this->listen($event.'_pushed', function() use ($event, $payload)
		{
			$this->fire($event, $payload);
		});
	}
	public function subscribe($subscriber)
	{
		$subscriber = $this->resolveSubscriber($subscriber);
		$subscriber->subscribe($this);
	}
	protected function resolveSubscriber($subscriber)
	{
		if (is_string($subscriber))
		{
			return $this->container->make($subscriber);
		}
		return $subscriber;
	}
	public function until($event, $payload = array())
	{
		return $this->fire($event, $payload, true);
	}
	public function flush($event)
	{
		$this->fire($event.'_pushed');
	}
	public function firing()
	{
		return last($this->firing);
	}
	public function fire($event, $payload = array(), $halt = false)
	{
		if (is_object($event))
		{
			list($payload, $event) = [[$event], get_class($event)];
		}
		$responses = array();
		if ( ! is_array($payload)) $payload = array($payload);
		$this->firing[] = $event;
		foreach ($this->getListeners($event) as $listener)
		{
			$response = call_user_func_array($listener, $payload);
			if ( ! is_null($response) && $halt)
			{
				array_pop($this->firing);
				return $response;
			}
			if ($response === false) break;
			$responses[] = $response;
		}
		array_pop($this->firing);
		return $halt ? null : $responses;
	}
	public function getListeners($eventName)
	{
		$wildcards = $this->getWildcardListeners($eventName);
		if ( ! isset($this->sorted[$eventName]))
		{
			$this->sortListeners($eventName);
		}
		return array_merge($this->sorted[$eventName], $wildcards);
	}
	protected function getWildcardListeners($eventName)
	{
		$wildcards = array();
		foreach ($this->wildcards as $key => $listeners)
		{
			if (str_is($key, $eventName)) $wildcards = array_merge($wildcards, $listeners);
		}
		return $wildcards;
	}
	protected function sortListeners($eventName)
	{
		$this->sorted[$eventName] = array();
		if (isset($this->listeners[$eventName]))
		{
			krsort($this->listeners[$eventName]);
			$this->sorted[$eventName] = call_user_func_array(
				'array_merge', $this->listeners[$eventName]
			);
		}
	}
	public function makeListener($listener)
	{
		return is_string($listener) ? $this->createClassListener($listener) : $listener;
	}
	public function createClassListener($listener)
	{
		$container = $this->container;
		return function() use ($listener, $container)
		{
			return call_user_func_array(
				$this->createClassCallable($listener, $container), func_get_args()
			);
		};
	}
	protected function createClassCallable($listener, $container)
	{
		list($class, $method) = $this->parseClassCallable($listener);
		if ($this->handlerShouldBeQueued($class))
		{
			return $this->createQueuedHandlerCallable($class, $method);
		}
		else
		{
			return array($container->make($class), $method);
		}
	}
	protected function parseClassCallable($listener)
	{
		$segments = explode('@', $listener);
		return [$segments[0], count($segments) == 2 ? $segments[1] : 'handle'];
	}
	protected function handlerShouldBeQueued($class)
	{
		try
		{
			return (new ReflectionClass($class))->implementsInterface(
				'Illuminate\Contracts\Queue\ShouldBeQueued'
			);
		}
		catch (Exception $e)
		{
			return false;
		}
	}
	protected function createQueuedHandlerCallable($class, $method)
	{
		return function() use ($class, $method)
		{
			$arguments = $this->cloneArgumentsForQueueing(func_get_args());
			if (method_exists($class, 'queue'))
			{
				$this->callQueueMethodOnHandler($class, $method, $arguments);
			}
			else
			{
				$this->resolveQueue()->push('Illuminate\Events\CallQueuedHandler@call', [
					'class' => $class, 'method' => $method, 'data' => serialize($arguments),
				]);
			}
		};
	}
	protected function cloneArgumentsForQueueing(array $arguments)
	{
		return array_map(function($a) { return is_object($a) ? clone $a : $a; }, $arguments);
	}
	protected function callQueueMethodOnHandler($class, $method, $arguments)
	{
		$handler = (new ReflectionClass($class))->newInstanceWithoutConstructor();
		$handler->queue($this->resolveQueue(), 'Illuminate\Events\CallQueuedHandler@call', [
			'class' => $class, 'method' => $method, 'data' => serialize($arguments),
		]);
	}
	public function forget($event)
	{
		unset($this->listeners[$event], $this->sorted[$event]);
	}
	public function forgetPushed()
	{
		foreach ($this->listeners as $key => $value)
		{
			if (ends_with($key, '_pushed')) $this->forget($key);
		}
	}
	protected function resolveQueue()
	{
		return call_user_func($this->queueResolver);
	}
	public function setQueueResolver(callable $resolver)
	{
		$this->queueResolver = $resolver;
		return $this;
	}
}
