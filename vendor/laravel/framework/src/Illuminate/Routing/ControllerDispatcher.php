<?php namespace Illuminate\Routing;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Container\Container;
class ControllerDispatcher {
	use RouteDependencyResolverTrait;
	protected $router;
	protected $container;
	public function __construct(Router $router,
								Container $container = null)
	{
		$this->router = $router;
		$this->container = $container;
	}
	public function dispatch(Route $route, Request $request, $controller, $method)
	{
		$instance = $this->makeController($controller);
		$this->assignAfter($instance, $route, $request, $method);
		$response = $this->before($instance, $route, $request, $method);
		if (is_null($response))
		{
			$response = $this->callWithinStack(
				$instance, $route, $request, $method
			);
		}
		return $response;
	}
	protected function makeController($controller)
	{
		Controller::setRouter($this->router);
		return $this->container->make($controller);
	}
	protected function callWithinStack($instance, $route, $request, $method)
	{
		$middleware = $this->getMiddleware($instance, $method);
		return (new Pipeline($this->container))
	                ->send($request)
	                ->through($middleware)
	                ->then(function($request) use ($instance, $route, $method)
					{
						return $this->call($instance, $route, $method);
					});
	}
	protected function getMiddleware($instance, $method)
	{
		$middleware = $this->router->getMiddleware();
		$results = [];
		foreach ($instance->getMiddleware() as $name => $options)
		{
			if ( ! $this->methodExcludedByOptions($method, $options))
			{
				$results[] = array_get($middleware, $name, $name);
			}
		}
		return $results;
	}
	public function methodExcludedByOptions($method, array $options)
	{
		return ( ! empty($options['only']) && ! in_array($method, (array) $options['only'])) ||
			( ! empty($options['except']) && in_array($method, (array) $options['except']));
	}
	protected function call($instance, $route, $method)
	{
		$parameters = $this->resolveClassMethodDependencies(
			$route->parametersWithoutNulls(), $instance, $method
		);
		return $instance->callAction($method, $parameters);
	}
	protected function before($instance, $route, $request, $method)
	{
		foreach ($instance->getBeforeFilters() as $filter)
		{
			if ($this->filterApplies($filter, $request, $method))
			{
				$response = $this->callFilter($filter, $route, $request);
				if ( ! is_null($response)) return $response;
			}
		}
	}
	protected function assignAfter($instance, $route, $request, $method)
	{
		foreach ($instance->getAfterFilters() as $filter)
		{
			if ($this->filterApplies($filter, $request, $method))
			{
				$route->after($this->getAssignableAfter($filter));
			}
		}
	}
	protected function getAssignableAfter($filter)
	{
		if ($filter['original'] instanceof Closure) return $filter['filter'];
		return $filter['original'];
	}
	protected function filterApplies($filter, $request, $method)
	{
		foreach (array('Method', 'On') as $type)
		{
			if ($this->{"filterFails{$type}"}($filter, $request, $method))
			{
				return false;
			}
		}
		return true;
	}
	protected function filterFailsMethod($filter, $request, $method)
	{
		return $this->methodExcludedByOptions($method, $filter['options']);
	}
	protected function filterFailsOn($filter, $request, $method)
	{
		$on = array_get($filter, 'options.on');
		if (is_null($on)) return false;
		if (is_string($on)) $on = explode('|', $on);
		return ! in_array(strtolower($request->getMethod()), $on);
	}
	protected function callFilter($filter, $route, $request)
	{
		return $this->router->callRouteFilter(
			$filter['filter'], $filter['parameters'], $route, $request
		);
	}
}
