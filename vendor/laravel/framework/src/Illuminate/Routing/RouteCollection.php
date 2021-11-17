<?php namespace Illuminate\Routing;
use Countable;
use ArrayIterator;
use IteratorAggregate;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
class RouteCollection implements Countable, IteratorAggregate {
	protected $routes = array();
	protected $allRoutes = array();
	protected $nameList = array();
	protected $actionList = array();
	public function add(Route $route)
	{
		$this->addToCollections($route);
		$this->addLookups($route);
		return $route;
	}
	protected function addToCollections($route)
	{
		$domainAndUri = $route->domain().$route->getUri();
		foreach ($route->methods() as $method)
		{
			$this->routes[$method][$domainAndUri] = $route;
		}
		$this->allRoutes[$method.$domainAndUri] = $route;
	}
	protected function addLookups($route)
	{
		$action = $route->getAction();
		if (isset($action['as']))
		{
			$this->nameList[$action['as']] = $route;
		}
		if (isset($action['controller']))
		{
			$this->addToActionList($action, $route);
		}
	}
	protected function addToActionList($action, $route)
	{
		$this->actionList[$action['controller']] = $route;
	}
	public function match(Request $request)
	{
		$routes = $this->get($request->getMethod());
		$route = $this->check($routes, $request);
		if ( ! is_null($route))
		{
			return $route->bind($request);
		}
		$others = $this->checkForAlternateVerbs($request);
		if (count($others) > 0)
		{
			return $this->getRouteForMethods($request, $others);
		}
		throw new NotFoundHttpException;
	}
	protected function checkForAlternateVerbs($request)
	{
		$methods = array_diff(Router::$verbs, array($request->getMethod()));
		$others = array();
		foreach ($methods as $method)
		{
			if ( ! is_null($this->check($this->get($method), $request, false)))
			{
				$others[] = $method;
			}
		}
		return $others;
	}
	protected function getRouteForMethods($request, array $methods)
	{
		if ($request->method() == 'OPTIONS')
		{
			return (new Route('OPTIONS', $request->path(), function() use ($methods)
			{
				return new Response('', 200, array('Allow' => implode(',', $methods)));
			}))->bind($request);
		}
		$this->methodNotAllowed($methods);
	}
	protected function methodNotAllowed(array $others)
	{
		throw new MethodNotAllowedHttpException($others);
	}
	protected function check(array $routes, $request, $includingMethod = true)
	{
		return array_first($routes, function($key, $value) use ($request, $includingMethod)
		{
			return $value->matches($request, $includingMethod);
		});
	}
	protected function get($method = null)
	{
		if (is_null($method)) return $this->getRoutes();
		return array_get($this->routes, $method, array());
	}
	public function hasNamedRoute($name)
	{
		return ! is_null($this->getByName($name));
	}
	public function getByName($name)
	{
		return isset($this->nameList[$name]) ? $this->nameList[$name] : null;
	}
	public function getByAction($action)
	{
		return isset($this->actionList[$action]) ? $this->actionList[$action] : null;
	}
	public function getRoutes()
	{
		return array_values($this->allRoutes);
	}
	public function getIterator()
	{
		return new ArrayIterator($this->getRoutes());
	}
	public function count()
	{
		return count($this->getRoutes());
	}
}
