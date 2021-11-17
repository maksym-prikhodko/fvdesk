<?php namespace Illuminate\Routing;
use ReflectionMethod;
use ReflectionFunctionAbstract;
trait RouteDependencyResolverTrait {
	protected function callWithDependencies($instance, $method)
	{
		return call_user_func_array(
			[$instance, $method], $this->resolveClassMethodDependencies([], $instance, $method)
		);
	}
	protected function resolveClassMethodDependencies(array $parameters, $instance, $method)
	{
		if ( ! method_exists($instance, $method)) return $parameters;
		return $this->resolveMethodDependencies(
			$parameters, new ReflectionMethod($instance, $method)
		);
	}
	public function resolveMethodDependencies(array $parameters, ReflectionFunctionAbstract $reflector)
	{
		foreach ($reflector->getParameters() as $key => $parameter)
		{
			$class = $parameter->getClass();
			if ($class && ! $this->alreadyInParameters($class->name, $parameters))
			{
				array_splice(
					$parameters, $key, 0, [$this->container->make($class->name)]
				);
			}
		}
		return $parameters;
	}
	protected function alreadyInParameters($class, array $parameters)
	{
		return ! is_null(array_first($parameters, function($key, $value) use ($class)
		{
			return $value instanceof $class;
		}));
	}
}
