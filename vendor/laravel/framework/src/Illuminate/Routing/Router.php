<?php namespace Illuminate\Routing;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Collection;
use Illuminate\Container\Container;
use Illuminate\Support\Traits\Macroable;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Routing\Registrar as RegistrarContract;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
class Router implements RegistrarContract {
	use Macroable;
	protected $events;
	protected $container;
	protected $routes;
	protected $current;
	protected $currentRequest;
	protected $middleware = [];
	protected $patternFilters = array();
	protected $regexFilters = array();
	protected $binders = array();
	protected $patterns = array();
	protected $groupStack = array();
	public static $verbs = array('GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS');
	public function __construct(Dispatcher $events, Container $container = null)
	{
		$this->events = $events;
		$this->routes = new RouteCollection;
		$this->container = $container ?: new Container;
	}
	public function get($uri, $action)
	{
		return $this->addRoute(['GET', 'HEAD'], $uri, $action);
	}
	public function post($uri, $action)
	{
		return $this->addRoute('POST', $uri, $action);
	}
	public function put($uri, $action)
	{
		return $this->addRoute('PUT', $uri, $action);
	}
	public function patch($uri, $action)
	{
		return $this->addRoute('PATCH', $uri, $action);
	}
	public function delete($uri, $action)
	{
		return $this->addRoute('DELETE', $uri, $action);
	}
	public function options($uri, $action)
	{
		return $this->addRoute('OPTIONS', $uri, $action);
	}
	public function any($uri, $action)
	{
		$verbs = array('GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE');
		return $this->addRoute($verbs, $uri, $action);
	}
	public function match($methods, $uri, $action)
	{
		return $this->addRoute(array_map('strtoupper', (array) $methods), $uri, $action);
	}
	public function controllers(array $controllers)
	{
		foreach ($controllers as $uri => $name)
		{
			$this->controller($uri, $name);
		}
	}
	public function controller($uri, $controller, $names = array())
	{
		$prepended = $controller;
		if ( ! empty($this->groupStack))
		{
			$prepended = $this->prependGroupUses($controller);
		}
		$routable = (new ControllerInspector)
							->getRoutable($prepended, $uri);
		foreach ($routable as $method => $routes)
		{
			foreach ($routes as $route)
			{
				$this->registerInspected($route, $controller, $method, $names);
			}
		}
		$this->addFallthroughRoute($controller, $uri);
	}
	protected function registerInspected($route, $controller, $method, &$names)
	{
		$action = array('uses' => $controller.'@'.$method);
		$action['as'] = array_get($names, $method);
		$this->{$route['verb']}($route['uri'], $action);
	}
	protected function addFallthroughRoute($controller, $uri)
	{
		$missing = $this->any($uri.'/{_missing}', $controller.'@missingMethod');
		$missing->where('_missing', '(.*)');
	}
	public function resources(array $resources)
	{
		foreach ($resources as $name => $controller)
		{
			$this->resource($name, $controller);
		}
	}
	public function resource($name, $controller, array $options = array())
	{
		if ($this->container && $this->container->bound('Illuminate\Routing\ResourceRegistrar'))
		{
			$registrar = $this->container->make('Illuminate\Routing\ResourceRegistrar');
		}
		else
		{
			$registrar = new ResourceRegistrar($this);
		}
		$registrar->register($name, $controller, $options);
	}
	public function group(array $attributes, Closure $callback)
	{
		$this->updateGroupStack($attributes);
		call_user_func($callback, $this);
		array_pop($this->groupStack);
	}
	protected function updateGroupStack(array $attributes)
	{
		if ( ! empty($this->groupStack))
		{
			$attributes = $this->mergeGroup($attributes, last($this->groupStack));
		}
		$this->groupStack[] = $attributes;
	}
	public function mergeWithLastGroup($new)
	{
		return $this->mergeGroup($new, last($this->groupStack));
	}
	public static function mergeGroup($new, $old)
	{
		$new['namespace'] = static::formatUsesPrefix($new, $old);
		$new['prefix'] = static::formatGroupPrefix($new, $old);
		if (isset($new['domain'])) unset($old['domain']);
		$new['where'] = array_merge(array_get($old, 'where', []), array_get($new, 'where', []));
		return array_merge_recursive(array_except($old, array('namespace', 'prefix', 'where')), $new);
	}
	protected static function formatUsesPrefix($new, $old)
	{
		if (isset($new['namespace']) && isset($old['namespace']))
		{
			return trim(array_get($old, 'namespace'), '\\').'\\'.trim($new['namespace'], '\\');
		}
		elseif (isset($new['namespace']))
		{
			return trim($new['namespace'], '\\');
		}
		return array_get($old, 'namespace');
	}
	protected static function formatGroupPrefix($new, $old)
	{
		if (isset($new['prefix']))
		{
			return trim(array_get($old, 'prefix'), '/').'/'.trim($new['prefix'], '/');
		}
		return array_get($old, 'prefix');
	}
	public function getLastGroupPrefix()
	{
		if ( ! empty($this->groupStack))
		{
			$last = end($this->groupStack);
			return isset($last['prefix']) ? $last['prefix'] : '';
		}
		return '';
	}
	protected function addRoute($methods, $uri, $action)
	{
		return $this->routes->add($this->createRoute($methods, $uri, $action));
	}
	protected function createRoute($methods, $uri, $action)
	{
		if ($this->actionReferencesController($action))
		{
			$action = $this->convertToControllerAction($action);
		}
		$route = $this->newRoute(
			$methods, $this->prefix($uri), $action
		);
		if ($this->hasGroupStack())
		{
			$this->mergeGroupAttributesIntoRoute($route);
		}
		$this->addWhereClausesToRoute($route);
		return $route;
	}
	protected function newRoute($methods, $uri, $action)
	{
		return (new Route($methods, $uri, $action))->setContainer($this->container);
	}
	protected function prefix($uri)
	{
		return trim(trim($this->getLastGroupPrefix(), '/').'/'.trim($uri, '/'), '/') ?: '/';
	}
	protected function addWhereClausesToRoute($route)
	{
		$route->where(
			array_merge($this->patterns, array_get($route->getAction(), 'where', []))
		);
		return $route;
	}
	protected function mergeGroupAttributesIntoRoute($route)
	{
		$action = $this->mergeWithLastGroup($route->getAction());
		$route->setAction($action);
	}
	protected function actionReferencesController($action)
	{
		if ($action instanceof Closure) return false;
		return is_string($action) || is_string(array_get($action, 'uses'));
	}
	protected function convertToControllerAction($action)
	{
		if (is_string($action)) $action = array('uses' => $action);
		if ( ! empty($this->groupStack))
		{
			$action['uses'] = $this->prependGroupUses($action['uses']);
		}
		$action['controller'] = $action['uses'];
		return $action;
	}
	protected function prependGroupUses($uses)
	{
		$group = last($this->groupStack);
		return isset($group['namespace']) && strpos($uses, '\\') !== 0 ? $group['namespace'].'\\'.$uses : $uses;
	}
	public function dispatch(Request $request)
	{
		$this->currentRequest = $request;
		$response = $this->callFilter('before', $request);
		if (is_null($response))
		{
			$response = $this->dispatchToRoute($request);
		}
		$response = $this->prepareResponse($request, $response);
		$this->callFilter('after', $request, $response);
		return $response;
	}
	public function dispatchToRoute(Request $request)
	{
		$route = $this->findRoute($request);
		$request->setRouteResolver(function() use ($route)
		{
			return $route;
		});
		$this->events->fire('router.matched', [$route, $request]);
		$response = $this->callRouteBefore($route, $request);
		if (is_null($response))
		{
			$response = $this->runRouteWithinStack(
				$route, $request
			);
		}
		$response = $this->prepareResponse($request, $response);
		$this->callRouteAfter($route, $request, $response);
		return $response;
	}
	protected function runRouteWithinStack(Route $route, Request $request)
	{
		$middleware = $this->gatherRouteMiddlewares($route);
		return (new Pipeline($this->container))
						->send($request)
						->through($middleware)
						->then(function($request) use ($route)
						{
							return $this->prepareResponse(
								$request,
								$route->run($request)
							);
						});
	}
	public function gatherRouteMiddlewares(Route $route)
	{
		return Collection::make($route->middleware())->map(function($m)
		{
			return Collection::make(array_get($this->middleware, $m, $m));
		})->collapse()->all();
	}
	protected function findRoute($request)
	{
		$this->current = $route = $this->routes->match($request);
		$this->container->instance('Illuminate\Routing\Route', $route);
		return $this->substituteBindings($route);
	}
	protected function substituteBindings($route)
	{
		foreach ($route->parameters() as $key => $value)
		{
			if (isset($this->binders[$key]))
			{
				$route->setParameter($key, $this->performBinding($key, $value, $route));
			}
		}
		return $route;
	}
	protected function performBinding($key, $value, $route)
	{
		return call_user_func($this->binders[$key], $value, $route);
	}
	public function matched($callback)
	{
		$this->events->listen('router.matched', $callback);
	}
	public function before($callback)
	{
		$this->addGlobalFilter('before', $callback);
	}
	public function after($callback)
	{
		$this->addGlobalFilter('after', $callback);
	}
	protected function addGlobalFilter($filter, $callback)
	{
		$this->events->listen('router.'.$filter, $this->parseFilter($callback));
	}
	public function getMiddleware()
	{
		return $this->middleware;
	}
	public function middleware($name, $class)
	{
		$this->middleware[$name] = $class;
		return $this;
	}
	public function filter($name, $callback)
	{
		$this->events->listen('router.filter: '.$name, $this->parseFilter($callback));
	}
	protected function parseFilter($callback)
	{
		if (is_string($callback) && ! str_contains($callback, '@'))
		{
			return $callback.'@filter';
		}
		return $callback;
	}
	public function when($pattern, $name, $methods = null)
	{
		if ( ! is_null($methods)) $methods = array_map('strtoupper', (array) $methods);
		$this->patternFilters[$pattern][] = compact('name', 'methods');
	}
	public function whenRegex($pattern, $name, $methods = null)
	{
		if ( ! is_null($methods)) $methods = array_map('strtoupper', (array) $methods);
		$this->regexFilters[$pattern][] = compact('name', 'methods');
	}
	public function model($key, $class, Closure $callback = null)
	{
		$this->bind($key, function($value) use ($class, $callback)
		{
			if (is_null($value)) return;
			if ($model = (new $class)->find($value))
			{
				return $model;
			}
			if ($callback instanceof Closure)
			{
				return call_user_func($callback, $value);
			}
			throw new NotFoundHttpException;
		});
	}
	public function bind($key, $binder)
	{
		if (is_string($binder))
		{
			$binder = $this->createClassBinding($binder);
		}
		$this->binders[str_replace('-', '_', $key)] = $binder;
	}
	public function createClassBinding($binding)
	{
		return function($value, $route) use ($binding)
		{
			$segments = explode('@', $binding);
			$method = count($segments) == 2 ? $segments[1] : 'bind';
			$callable = [$this->container->make($segments[0]), $method];
			return call_user_func($callable, $value, $route);
		};
	}
	public function pattern($key, $pattern)
	{
		$this->patterns[$key] = $pattern;
	}
	public function patterns($patterns)
	{
		foreach ($patterns as $key => $pattern)
		{
			$this->pattern($key, $pattern);
		}
	}
	protected function callFilter($filter, $request, $response = null)
	{
		return $this->events->until('router.'.$filter, array($request, $response));
	}
	public function callRouteBefore($route, $request)
	{
		$response = $this->callPatternFilters($route, $request);
		return $response ?: $this->callAttachedBefores($route, $request);
	}
	protected function callPatternFilters($route, $request)
	{
		foreach ($this->findPatternFilters($request) as $filter => $parameters)
		{
			$response = $this->callRouteFilter($filter, $parameters, $route, $request);
			if ( ! is_null($response)) return $response;
		}
	}
	public function findPatternFilters($request)
	{
		$results = array();
		list($path, $method) = array($request->path(), $request->getMethod());
		foreach ($this->patternFilters as $pattern => $filters)
		{
			if (str_is($pattern, $path))
			{
				$merge = $this->patternsByMethod($method, $filters);
				$results = array_merge($results, $merge);
			}
		}
		foreach ($this->regexFilters as $pattern => $filters)
		{
			if (preg_match($pattern, $path))
			{
				$merge = $this->patternsByMethod($method, $filters);
				$results = array_merge($results, $merge);
			}
		}
		return $results;
	}
	protected function patternsByMethod($method, $filters)
	{
		$results = array();
		foreach ($filters as $filter)
		{
			if ($this->filterSupportsMethod($filter, $method))
			{
				$parsed = Route::parseFilters($filter['name']);
				$results = array_merge($results, $parsed);
			}
		}
		return $results;
	}
	protected function filterSupportsMethod($filter, $method)
	{
		$methods = $filter['methods'];
		return is_null($methods) || in_array($method, $methods);
	}
	protected function callAttachedBefores($route, $request)
	{
		foreach ($route->beforeFilters() as $filter => $parameters)
		{
			$response = $this->callRouteFilter($filter, $parameters, $route, $request);
			if ( ! is_null($response)) return $response;
		}
	}
	public function callRouteAfter($route, $request, $response)
	{
		foreach ($route->afterFilters() as $filter => $parameters)
		{
			$this->callRouteFilter($filter, $parameters, $route, $request, $response);
		}
	}
	public function callRouteFilter($filter, $parameters, $route, $request, $response = null)
	{
		$data = array_merge(array($route, $request, $response), $parameters);
		return $this->events->until('router.filter: '.$filter, $this->cleanFilterParameters($data));
	}
	protected function cleanFilterParameters(array $parameters)
	{
		return array_filter($parameters, function($p)
		{
			return ! is_null($p) && $p !== '';
		});
	}
	protected function prepareResponse($request, $response)
	{
		if ( ! $response instanceof SymfonyResponse)
		{
			$response = new Response($response);
		}
		return $response->prepare($request);
	}
	public function hasGroupStack()
	{
		return ! empty($this->groupStack);
	}
	public function getGroupStack()
	{
		return $this->groupStack;
	}
	public function input($key, $default = null)
	{
		return $this->current()->parameter($key, $default);
	}
	public function getCurrentRoute()
	{
		return $this->current();
	}
	public function current()
	{
		return $this->current;
	}
	public function has($name)
	{
		return $this->routes->hasNamedRoute($name);
	}
	public function currentRouteName()
	{
		return $this->current() ? $this->current()->getName() : null;
	}
	public function is()
	{
		foreach (func_get_args() as $pattern)
		{
			if (str_is($pattern, $this->currentRouteName()))
			{
				return true;
			}
		}
		return false;
	}
	public function currentRouteNamed($name)
	{
		return $this->current() ? $this->current()->getName() == $name : false;
	}
	public function currentRouteAction()
	{
		if ( ! $this->current()) return;
		$action = $this->current()->getAction();
		return isset($action['controller']) ? $action['controller'] : null;
	}
	public function uses()
	{
		foreach (func_get_args() as $pattern)
		{
			if (str_is($pattern, $this->currentRouteAction()))
			{
				return true;
			}
		}
		return false;
	}
	public function currentRouteUses($action)
	{
		return $this->currentRouteAction() == $action;
	}
	public function getCurrentRequest()
	{
		return $this->currentRequest;
	}
	public function getRoutes()
	{
		return $this->routes;
	}
	public function setRoutes(RouteCollection $routes)
	{
		foreach ($routes as $route)
		{
			$route->setContainer($this->container);
		}
		$this->routes = $routes;
		$this->container->instance('routes', $this->routes);
	}
	public function getPatterns()
	{
		return $this->patterns;
	}
}
