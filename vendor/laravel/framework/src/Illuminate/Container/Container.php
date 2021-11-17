<?php namespace Illuminate\Container;
use Closure;
use ArrayAccess;
use ReflectionClass;
use ReflectionMethod;
use ReflectionFunction;
use ReflectionParameter;
use InvalidArgumentException;
use Illuminate\Contracts\Container\Container as ContainerContract;
class Container implements ArrayAccess, ContainerContract {
	protected static $instance;
	protected $resolved = [];
	protected $bindings = [];
	protected $instances = [];
	protected $aliases = [];
	protected $extenders = [];
	protected $tags = [];
	protected $buildStack = [];
	public $contextual = [];
	protected $reboundCallbacks = [];
	protected $globalResolvingCallbacks = [];
	protected $globalAfterResolvingCallbacks = [];
	protected $resolvingCallbacks = [];
	protected $afterResolvingCallbacks = [];
	public function when($concrete)
	{
		return new ContextualBindingBuilder($this, $concrete);
	}
	protected function resolvable($abstract)
	{
		return $this->bound($abstract);
	}
	public function bound($abstract)
	{
		return isset($this->bindings[$abstract]) || isset($this->instances[$abstract]) || $this->isAlias($abstract);
	}
	public function resolved($abstract)
	{
		return isset($this->resolved[$abstract]) || isset($this->instances[$abstract]);
	}
	public function isAlias($name)
	{
		return isset($this->aliases[$name]);
	}
	public function bind($abstract, $concrete = null, $shared = false)
	{
		if (is_array($abstract))
		{
			list($abstract, $alias) = $this->extractAlias($abstract);
			$this->alias($abstract, $alias);
		}
		$this->dropStaleInstances($abstract);
		if (is_null($concrete))
		{
			$concrete = $abstract;
		}
		if ( ! $concrete instanceof Closure)
		{
			$concrete = $this->getClosure($abstract, $concrete);
		}
		$this->bindings[$abstract] = compact('concrete', 'shared');
		if ($this->resolved($abstract))
		{
			$this->rebound($abstract);
		}
	}
	protected function getClosure($abstract, $concrete)
	{
		return function($c, $parameters = []) use ($abstract, $concrete)
		{
			$method = ($abstract == $concrete) ? 'build' : 'make';
			return $c->$method($concrete, $parameters);
		};
	}
	public function addContextualBinding($concrete, $abstract, $implementation)
	{
		$this->contextual[$concrete][$abstract] = $implementation;
	}
	public function bindIf($abstract, $concrete = null, $shared = false)
	{
		if ( ! $this->bound($abstract))
		{
			$this->bind($abstract, $concrete, $shared);
		}
	}
	public function singleton($abstract, $concrete = null)
	{
		$this->bind($abstract, $concrete, true);
	}
	public function share(Closure $closure)
	{
		return function($container) use ($closure)
		{
			static $object;
			if (is_null($object))
			{
				$object = $closure($container);
			}
			return $object;
		};
	}
	public function bindShared($abstract, Closure $closure)
	{
		$this->bind($abstract, $this->share($closure), true);
	}
	public function extend($abstract, Closure $closure)
	{
		if (isset($this->instances[$abstract]))
		{
			$this->instances[$abstract] = $closure($this->instances[$abstract], $this);
			$this->rebound($abstract);
		}
		else
		{
			$this->extenders[$abstract][] = $closure;
		}
	}
	public function instance($abstract, $instance)
	{
		if (is_array($abstract))
		{
			list($abstract, $alias) = $this->extractAlias($abstract);
			$this->alias($abstract, $alias);
		}
		unset($this->aliases[$abstract]);
		$bound = $this->bound($abstract);
		$this->instances[$abstract] = $instance;
		if ($bound)
		{
			$this->rebound($abstract);
		}
	}
	public function tag($abstracts, $tags)
	{
		$tags = is_array($tags) ? $tags : array_slice(func_get_args(), 1);
		foreach ($tags as $tag)
		{
			if ( ! isset($this->tags[$tag])) $this->tags[$tag] = [];
			foreach ((array) $abstracts as $abstract)
			{
				$this->tags[$tag][] = $abstract;
			}
		}
	}
	public function tagged($tag)
	{
		$results = [];
		foreach ($this->tags[$tag] as $abstract)
		{
			$results[] = $this->make($abstract);
		}
		return $results;
	}
	public function alias($abstract, $alias)
	{
		$this->aliases[$alias] = $abstract;
	}
	protected function extractAlias(array $definition)
	{
		return [key($definition), current($definition)];
	}
	public function rebinding($abstract, Closure $callback)
	{
		$this->reboundCallbacks[$abstract][] = $callback;
		if ($this->bound($abstract)) return $this->make($abstract);
	}
	public function refresh($abstract, $target, $method)
	{
		return $this->rebinding($abstract, function($app, $instance) use ($target, $method)
		{
			$target->{$method}($instance);
		});
	}
	protected function rebound($abstract)
	{
		$instance = $this->make($abstract);
		foreach ($this->getReboundCallbacks($abstract) as $callback)
		{
			call_user_func($callback, $this, $instance);
		}
	}
	protected function getReboundCallbacks($abstract)
	{
		if (isset($this->reboundCallbacks[$abstract]))
		{
			return $this->reboundCallbacks[$abstract];
		}
		return [];
	}
	public function wrap(Closure $callback, array $parameters = [])
	{
		return function() use ($callback, $parameters)
		{
			return $this->call($callback, $parameters);
		};
	}
	public function call($callback, array $parameters = [], $defaultMethod = null)
	{
		if ($this->isCallableWithAtSign($callback) || $defaultMethod)
		{
			return $this->callClass($callback, $parameters, $defaultMethod);
		}
		$dependencies = $this->getMethodDependencies($callback, $parameters);
		return call_user_func_array($callback, $dependencies);
	}
	protected function isCallableWithAtSign($callback)
	{
		if ( ! is_string($callback)) return false;
		return strpos($callback, '@') !== false;
	}
	protected function getMethodDependencies($callback, $parameters = [])
	{
		$dependencies = [];
		foreach ($this->getCallReflector($callback)->getParameters() as $key => $parameter)
		{
			$this->addDependencyForCallParameter($parameter, $parameters, $dependencies);
		}
		return array_merge($dependencies, $parameters);
	}
	protected function getCallReflector($callback)
	{
		if (is_string($callback) && strpos($callback, '::') !== false)
		{
			$callback = explode('::', $callback);
		}
		if (is_array($callback))
		{
			return new ReflectionMethod($callback[0], $callback[1]);
		}
		return new ReflectionFunction($callback);
	}
	protected function addDependencyForCallParameter(ReflectionParameter $parameter, array &$parameters, &$dependencies)
	{
		if (array_key_exists($parameter->name, $parameters))
		{
			$dependencies[] = $parameters[$parameter->name];
			unset($parameters[$parameter->name]);
		}
		elseif ($parameter->getClass())
		{
			$dependencies[] = $this->make($parameter->getClass()->name);
		}
		elseif ($parameter->isDefaultValueAvailable())
		{
			$dependencies[] = $parameter->getDefaultValue();
		}
	}
	protected function callClass($target, array $parameters = [], $defaultMethod = null)
	{
		$segments = explode('@', $target);
		$method = count($segments) == 2 ? $segments[1] : $defaultMethod;
		if (is_null($method))
		{
			throw new InvalidArgumentException("Method not provided.");
		}
		return $this->call([$this->make($segments[0]), $method], $parameters);
	}
	public function make($abstract, $parameters = [])
	{
		$abstract = $this->getAlias($abstract);
		if (isset($this->instances[$abstract]))
		{
			return $this->instances[$abstract];
		}
		$concrete = $this->getConcrete($abstract);
		if ($this->isBuildable($concrete, $abstract))
		{
			$object = $this->build($concrete, $parameters);
		}
		else
		{
			$object = $this->make($concrete, $parameters);
		}
		foreach ($this->getExtenders($abstract) as $extender)
		{
			$object = $extender($object, $this);
		}
		if ($this->isShared($abstract))
		{
			$this->instances[$abstract] = $object;
		}
		$this->fireResolvingCallbacks($abstract, $object);
		$this->resolved[$abstract] = true;
		return $object;
	}
	protected function getConcrete($abstract)
	{
		if ( ! is_null($concrete = $this->getContextualConcrete($abstract)))
		{
			return $concrete;
		}
		if ( ! isset($this->bindings[$abstract]))
		{
			if ($this->missingLeadingSlash($abstract) &&
				isset($this->bindings['\\'.$abstract]))
			{
				$abstract = '\\'.$abstract;
			}
			return $abstract;
		}
		return $this->bindings[$abstract]['concrete'];
	}
	protected function getContextualConcrete($abstract)
	{
		if (isset($this->contextual[end($this->buildStack)][$abstract]))
		{
			return $this->contextual[end($this->buildStack)][$abstract];
		}
	}
	protected function missingLeadingSlash($abstract)
	{
		return is_string($abstract) && strpos($abstract, '\\') !== 0;
	}
	protected function getExtenders($abstract)
	{
		if (isset($this->extenders[$abstract]))
		{
			return $this->extenders[$abstract];
		}
		return [];
	}
	public function build($concrete, $parameters = [])
	{
		if ($concrete instanceof Closure)
		{
			return $concrete($this, $parameters);
		}
		$reflector = new ReflectionClass($concrete);
		if ( ! $reflector->isInstantiable())
		{
			$message = "Target [$concrete] is not instantiable.";
			throw new BindingResolutionException($message);
		}
		$this->buildStack[] = $concrete;
		$constructor = $reflector->getConstructor();
		if (is_null($constructor))
		{
			array_pop($this->buildStack);
			return new $concrete;
		}
		$dependencies = $constructor->getParameters();
		$parameters = $this->keyParametersByArgument(
			$dependencies, $parameters
		);
		$instances = $this->getDependencies(
			$dependencies, $parameters
		);
		array_pop($this->buildStack);
		return $reflector->newInstanceArgs($instances);
	}
	protected function getDependencies($parameters, array $primitives = [])
	{
		$dependencies = [];
		foreach ($parameters as $parameter)
		{
			$dependency = $parameter->getClass();
			if (array_key_exists($parameter->name, $primitives))
			{
				$dependencies[] = $primitives[$parameter->name];
			}
			elseif (is_null($dependency))
			{
				$dependencies[] = $this->resolveNonClass($parameter);
			}
			else
			{
				$dependencies[] = $this->resolveClass($parameter);
			}
		}
		return (array) $dependencies;
	}
	protected function resolveNonClass(ReflectionParameter $parameter)
	{
		if ($parameter->isDefaultValueAvailable())
		{
			return $parameter->getDefaultValue();
		}
		$message = "Unresolvable dependency resolving [$parameter] in class {$parameter->getDeclaringClass()->getName()}";
		throw new BindingResolutionException($message);
	}
	protected function resolveClass(ReflectionParameter $parameter)
	{
		try
		{
			return $this->make($parameter->getClass()->name);
		}
		catch (BindingResolutionException $e)
		{
			if ($parameter->isOptional())
			{
				return $parameter->getDefaultValue();
			}
			throw $e;
		}
	}
	protected function keyParametersByArgument(array $dependencies, array $parameters)
	{
		foreach ($parameters as $key => $value)
		{
			if (is_numeric($key))
			{
				unset($parameters[$key]);
				$parameters[$dependencies[$key]->name] = $value;
			}
		}
		return $parameters;
	}
	public function resolving($abstract, Closure $callback = null)
	{
		if ($callback === null && $abstract instanceof Closure)
		{
			$this->resolvingCallback($abstract);
		}
		else
		{
			$this->resolvingCallbacks[$abstract][] = $callback;
		}
	}
	public function afterResolving($abstract, Closure $callback = null)
	{
		if ($abstract instanceof Closure && $callback === null)
		{
			$this->afterResolvingCallback($abstract);
		}
		else
		{
			$this->afterResolvingCallbacks[$abstract][] = $callback;
		}
	}
	protected function resolvingCallback(Closure $callback)
	{
		$abstract = $this->getFunctionHint($callback);
		if ($abstract)
		{
			$this->resolvingCallbacks[$abstract][] = $callback;
		}
		else
		{
			$this->globalResolvingCallbacks[] = $callback;
		}
	}
	protected function afterResolvingCallback(Closure $callback)
	{
		$abstract = $this->getFunctionHint($callback);
		if ($abstract)
		{
			$this->afterResolvingCallbacks[$abstract][] = $callback;
		}
		else
		{
			$this->globalAfterResolvingCallbacks[] = $callback;
		}
	}
	protected function getFunctionHint(Closure $callback)
	{
		$function = new ReflectionFunction($callback);
		if ($function->getNumberOfParameters() == 0)
		{
			return;
		}
		$expected = $function->getParameters()[0];
		if ( ! $expected->getClass())
		{
			return;
		}
		return $expected->getClass()->name;
	}
	protected function fireResolvingCallbacks($abstract, $object)
	{
		$this->fireCallbackArray($object, $this->globalResolvingCallbacks);
		$this->fireCallbackArray(
			$object, $this->getCallbacksForType(
				$abstract, $object, $this->resolvingCallbacks
			)
		);
		$this->fireCallbackArray($object, $this->globalAfterResolvingCallbacks);
		$this->fireCallbackArray(
			$object, $this->getCallbacksForType(
				$abstract, $object, $this->afterResolvingCallbacks
			)
		);
	}
	protected function getCallbacksForType($abstract, $object, array $callbacksPerType)
	{
		$results = [];
		foreach ($callbacksPerType as $type => $callbacks)
		{
			if ($type === $abstract || $object instanceof $type)
			{
				$results = array_merge($results, $callbacks);
			}
		}
		return $results;
	}
	protected function fireCallbackArray($object, array $callbacks)
	{
		foreach ($callbacks as $callback)
		{
			$callback($object, $this);
		}
	}
	public function isShared($abstract)
	{
		if (isset($this->bindings[$abstract]['shared']))
		{
			$shared = $this->bindings[$abstract]['shared'];
		}
		else
		{
			$shared = false;
		}
		return isset($this->instances[$abstract]) || $shared === true;
	}
	protected function isBuildable($concrete, $abstract)
	{
		return $concrete === $abstract || $concrete instanceof Closure;
	}
	protected function getAlias($abstract)
	{
		return isset($this->aliases[$abstract]) ? $this->aliases[$abstract] : $abstract;
	}
	public function getBindings()
	{
		return $this->bindings;
	}
	protected function dropStaleInstances($abstract)
	{
		unset($this->instances[$abstract], $this->aliases[$abstract]);
	}
	public function forgetInstance($abstract)
	{
		unset($this->instances[$abstract]);
	}
	public function forgetInstances()
	{
		$this->instances = [];
	}
	public function flush()
	{
		$this->aliases = [];
		$this->resolved = [];
		$this->bindings = [];
		$this->instances = [];
	}
	public static function getInstance()
	{
		return static::$instance;
	}
	public static function setInstance(ContainerContract $container)
	{
		static::$instance = $container;
	}
	public function offsetExists($key)
	{
		return isset($this->bindings[$key]);
	}
	public function offsetGet($key)
	{
		return $this->make($key);
	}
	public function offsetSet($key, $value)
	{
		if ( ! $value instanceof Closure)
		{
			$value = function() use ($value)
			{
				return $value;
			};
		}
		$this->bind($key, $value);
	}
	public function offsetUnset($key)
	{
		unset($this->bindings[$key], $this->instances[$key], $this->resolved[$key]);
	}
	public function __get($key)
	{
		return $this[$key];
	}
	public function __set($key, $value)
	{
		$this[$key] = $value;
	}
}
