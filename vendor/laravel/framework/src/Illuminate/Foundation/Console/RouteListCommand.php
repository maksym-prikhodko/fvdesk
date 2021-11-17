<?php namespace Illuminate\Foundation\Console;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\Console\Command;
use Illuminate\Routing\Controller;
use Symfony\Component\Console\Input\InputOption;
class RouteListCommand extends Command {
	protected $name = 'route:list';
	protected $description = 'List all registered routes';
	protected $router;
	protected $routes;
	protected $headers = array(
		'Domain', 'Method', 'URI', 'Name', 'Action', 'Middleware',
	);
	public function __construct(Router $router)
	{
		parent::__construct();
		$this->router = $router;
		$this->routes = $router->getRoutes();
	}
	public function fire()
	{
		if (count($this->routes) == 0)
		{
			return $this->error("Your application doesn't have any routes.");
		}
		$this->displayRoutes($this->getRoutes());
	}
	protected function getRoutes()
	{
		$results = array();
		foreach ($this->routes as $route)
		{
			$results[] = $this->getRouteInformation($route);
		}
		return array_filter($results);
	}
	protected function getRouteInformation(Route $route)
	{
		return $this->filterRoute(array(
			'host'   => $route->domain(),
			'method' => implode('|', $route->methods()),
			'uri'    => $route->uri(),
			'name'   => $route->getName(),
			'action' => $route->getActionName(),
			'middleware' => $this->getMiddleware($route),
		));
	}
	protected function displayRoutes(array $routes)
	{
		$this->table($this->headers, $routes);
	}
	protected function getMiddleware($route)
	{
		$middlewares = array_values($route->middleware());
		$middlewares = array_unique(
			array_merge($middlewares, $this->getPatternFilters($route))
		);
		$actionName = $route->getActionName();
		if ( ! empty($actionName) && $actionName !== 'Closure')
		{
			$middlewares = array_merge($middlewares, $this->getControllerMiddleware($actionName));
		}
		return implode(',', $middlewares);
	}
	protected function getControllerMiddleware($actionName)
	{
		Controller::setRouter($this->laravel['router']);
		$segments = explode('@', $actionName);
		return $this->getControllerMiddlewareFromInstance(
			$this->laravel->make($segments[0]), $segments[1]
		);
	}
	protected function getControllerMiddlewareFromInstance($controller, $method)
	{
		$middleware = $this->router->getMiddleware();
		$results = [];
		foreach ($controller->getMiddleware() as $name => $options)
		{
			if ( ! $this->methodExcludedByOptions($method, $options))
			{
				$results[] = array_get($middleware, $name, $name);
			}
		}
		return $results;
	}
	protected function methodExcludedByOptions($method, array $options)
	{
		return ( ! empty($options['only']) && ! in_array($method, (array) $options['only'])) ||
			( ! empty($options['except']) && in_array($method, (array) $options['except']));
	}
	protected function getPatternFilters($route)
	{
		$patterns = array();
		foreach ($route->methods() as $method)
		{
			$inner = $this->getMethodPatterns($route->uri(), $method);
			$patterns = array_merge($patterns, array_keys($inner));
		}
		return $patterns;
	}
	protected function getMethodPatterns($uri, $method)
	{
		return $this->router->findPatternFilters(
			Request::create($uri, $method)
		);
	}
	protected function filterRoute(array $route)
	{
		if (($this->option('name') && ! str_contains($route['name'], $this->option('name'))) ||
			 $this->option('path') && ! str_contains($route['uri'], $this->option('path')))
		{
			return;
		}
		return $route;
	}
	protected function getOptions()
	{
		return array(
			array('name', null, InputOption::VALUE_OPTIONAL, 'Filter the routes by name.'),
			array('path', null, InputOption::VALUE_OPTIONAL, 'Filter the routes by path.'),
		);
	}
}
