<?php namespace Illuminate\Routing;
use Closure;
use BadMethodCallException;
use InvalidArgumentException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
abstract class Controller {
	protected $middleware = [];
	protected $beforeFilters = array();
	protected $afterFilters = array();
	protected static $router;
	public function middleware($middleware, array $options = array())
	{
		$this->middleware[$middleware] = $options;
	}
	public function beforeFilter($filter, array $options = array())
	{
		$this->beforeFilters[] = $this->parseFilter($filter, $options);
	}
	public function afterFilter($filter, array $options = array())
	{
		$this->afterFilters[] = $this->parseFilter($filter, $options);
	}
	protected function parseFilter($filter, array $options)
	{
		$parameters = array();
		$original = $filter;
		if ($filter instanceof Closure)
		{
			$filter = $this->registerClosureFilter($filter);
		}
		elseif ($this->isInstanceFilter($filter))
		{
			$filter = $this->registerInstanceFilter($filter);
		}
		else
		{
			list($filter, $parameters) = Route::parseFilter($filter);
		}
		return compact('original', 'filter', 'parameters', 'options');
	}
	protected function registerClosureFilter(Closure $filter)
	{
		$this->getRouter()->filter($name = spl_object_hash($filter), $filter);
		return $name;
	}
	protected function registerInstanceFilter($filter)
	{
		$this->getRouter()->filter($filter, array($this, substr($filter, 1)));
		return $filter;
	}
	protected function isInstanceFilter($filter)
	{
		if (is_string($filter) && starts_with($filter, '@'))
		{
			if (method_exists($this, substr($filter, 1))) return true;
			throw new InvalidArgumentException("Filter method [$filter] does not exist.");
		}
		return false;
	}
	public function forgetBeforeFilter($filter)
	{
		$this->beforeFilters = $this->removeFilter($filter, $this->getBeforeFilters());
	}
	public function forgetAfterFilter($filter)
	{
		$this->afterFilters = $this->removeFilter($filter, $this->getAfterFilters());
	}
	protected function removeFilter($removing, $current)
	{
		return array_filter($current, function($filter) use ($removing)
		{
			return $filter['original'] != $removing;
		});
	}
	public function getMiddleware()
	{
		return $this->middleware;
	}
	public function getBeforeFilters()
	{
		return $this->beforeFilters;
	}
	public function getAfterFilters()
	{
		return $this->afterFilters;
	}
	public static function getRouter()
	{
		return static::$router;
	}
	public static function setRouter(Router $router)
	{
		static::$router = $router;
	}
	public function callAction($method, $parameters)
	{
		return call_user_func_array(array($this, $method), $parameters);
	}
	public function missingMethod($parameters = array())
	{
		throw new NotFoundHttpException("Controller method not found.");
	}
	public function __call($method, $parameters)
	{
		throw new BadMethodCallException("Method [$method] does not exist.");
	}
}
