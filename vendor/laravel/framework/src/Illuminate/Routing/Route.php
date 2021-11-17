<?php namespace Illuminate\Routing;
use Closure;
use LogicException;
use ReflectionFunction;
use Illuminate\Http\Request;
use Illuminate\Container\Container;
use Illuminate\Routing\Matching\UriValidator;
use Illuminate\Routing\Matching\HostValidator;
use Illuminate\Routing\Matching\MethodValidator;
use Illuminate\Routing\Matching\SchemeValidator;
use Symfony\Component\Routing\Route as SymfonyRoute;
use Illuminate\Http\Exception\HttpResponseException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
class Route {
	use RouteDependencyResolverTrait;
	protected $uri;
	protected $methods;
	protected $action;
	protected $defaults = array();
	protected $wheres = array();
	protected $parameters;
	protected $parameterNames;
	protected $compiled;
	protected $container;
	public static $validators;
	public function __construct($methods, $uri, $action)
	{
		$this->uri = $uri;
		$this->methods = (array) $methods;
		$this->action = $this->parseAction($action);
		if (in_array('GET', $this->methods) && ! in_array('HEAD', $this->methods))
		{
			$this->methods[] = 'HEAD';
		}
		if (isset($this->action['prefix']))
		{
			$this->prefix($this->action['prefix']);
		}
	}
	public function run(Request $request)
	{
		$this->container = $this->container ?: new Container;
		try
		{
			if ( ! is_string($this->action['uses']))
			{
				return $this->runCallable($request);
			}
			if ($this->customDispatcherIsBound())
			{
				return $this->runWithCustomDispatcher($request);
			}
			return $this->runController($request);
		}
		catch (HttpResponseException $e)
		{
			return $e->getResponse();
		}
	}
	protected function runCallable(Request $request)
	{
		$parameters = $this->resolveMethodDependencies(
			$this->parametersWithoutNulls(), new ReflectionFunction($this->action['uses'])
		);
		return call_user_func_array($this->action['uses'], $parameters);
	}
	protected function runController(Request $request)
	{
		list($class, $method) = explode('@', $this->action['uses']);
		$parameters = $this->resolveClassMethodDependencies(
			$this->parametersWithoutNulls(), $class, $method
		);
		if ( ! method_exists($instance = $this->container->make($class), $method))
		{
			throw new NotFoundHttpException;
		}
		return call_user_func_array([$instance, $method], $parameters);
	}
	protected function customDispatcherIsBound()
	{
		return $this->container->bound('illuminate.route.dispatcher');
	}
	protected function runWithCustomDispatcher(Request $request)
	{
		list($class, $method) = explode('@', $this->action['uses']);
		$dispatcher = $this->container->make('illuminate.route.dispatcher');
		return $dispatcher->dispatch($this, $request, $class, $method);
	}
	public function matches(Request $request, $includingMethod = true)
	{
		$this->compileRoute();
		foreach ($this->getValidators() as $validator)
		{
			if ( ! $includingMethod && $validator instanceof MethodValidator) continue;
			if ( ! $validator->matches($this, $request)) return false;
		}
		return true;
	}
	protected function compileRoute()
	{
		$optionals = $this->extractOptionalParameters();
		$uri = preg_replace('/\{(\w+?)\?\}/', '{$1}', $this->uri);
		$this->compiled = with(
			new SymfonyRoute($uri, $optionals, $this->wheres, array(), $this->domain() ?: '')
		)->compile();
	}
	protected function extractOptionalParameters()
	{
		preg_match_all('/\{(\w+?)\?\}/', $this->uri, $matches);
		return isset($matches[1]) ? array_fill_keys($matches[1], null) : [];
	}
	public function middleware()
	{
		return (array) array_get($this->action, 'middleware', []);
	}
	public function beforeFilters()
	{
		if ( ! isset($this->action['before'])) return array();
		return $this->parseFilters($this->action['before']);
	}
	public function afterFilters()
	{
		if ( ! isset($this->action['after'])) return array();
		return $this->parseFilters($this->action['after']);
	}
	public static function parseFilters($filters)
	{
		return array_build(static::explodeFilters($filters), function($key, $value)
		{
			return Route::parseFilter($value);
		});
	}
	protected static function explodeFilters($filters)
	{
		if (is_array($filters)) return static::explodeArrayFilters($filters);
		return array_map('trim', explode('|', $filters));
	}
	protected static function explodeArrayFilters(array $filters)
	{
		$results = array();
		foreach ($filters as $filter)
		{
			$results = array_merge($results, array_map('trim', explode('|', $filter)));
		}
		return $results;
	}
	public static function parseFilter($filter)
	{
		if ( ! str_contains($filter, ':')) return array($filter, array());
		return static::parseParameterFilter($filter);
	}
	protected static function parseParameterFilter($filter)
	{
		list($name, $parameters) = explode(':', $filter, 2);
		return array($name, explode(',', $parameters));
	}
	public function hasParameter($name)
	{
		return array_key_exists($name, $this->parameters());
	}
	public function getParameter($name, $default = null)
	{
		return $this->parameter($name, $default);
	}
	public function parameter($name, $default = null)
	{
		return array_get($this->parameters(), $name, $default);
	}
	public function setParameter($name, $value)
	{
		$this->parameters();
		$this->parameters[$name] = $value;
	}
	public function forgetParameter($name)
	{
		$this->parameters();
		unset($this->parameters[$name]);
	}
	public function parameters()
	{
		if (isset($this->parameters))
		{
			return array_map(function($value)
			{
				return is_string($value) ? rawurldecode($value) : $value;
			}, $this->parameters);
		}
		throw new LogicException("Route is not bound.");
	}
	public function parametersWithoutNulls()
	{
		return array_filter($this->parameters(), function($p) { return ! is_null($p); });
	}
	public function parameterNames()
	{
		if (isset($this->parameterNames)) return $this->parameterNames;
		return $this->parameterNames = $this->compileParameterNames();
	}
	protected function compileParameterNames()
	{
		preg_match_all('/\{(.*?)\}/', $this->domain().$this->uri, $matches);
		return array_map(function($m) { return trim($m, '?'); }, $matches[1]);
	}
	public function bind(Request $request)
	{
		$this->compileRoute();
		$this->bindParameters($request);
		return $this;
	}
	public function bindParameters(Request $request)
	{
		$params = $this->matchToKeys(
			array_slice($this->bindPathParameters($request), 1)
		);
		if ( ! is_null($this->compiled->getHostRegex()))
		{
			$params = $this->bindHostParameters(
				$request, $params
			);
		}
		return $this->parameters = $this->replaceDefaults($params);
	}
	protected function bindPathParameters(Request $request)
	{
		preg_match($this->compiled->getRegex(), '/'.$request->decodedPath(), $matches);
		return $matches;
	}
	protected function bindHostParameters(Request $request, $parameters)
	{
		preg_match($this->compiled->getHostRegex(), $request->getHost(), $matches);
		return array_merge($this->matchToKeys(array_slice($matches, 1)), $parameters);
	}
	protected function matchToKeys(array $matches)
	{
		if (count($this->parameterNames()) == 0) return array();
		$parameters = array_intersect_key($matches, array_flip($this->parameterNames()));
		return array_filter($parameters, function($value)
		{
			return is_string($value) && strlen($value) > 0;
		});
	}
	protected function replaceDefaults(array $parameters)
	{
		foreach ($parameters as $key => &$value)
		{
			$value = isset($value) ? $value : array_get($this->defaults, $key);
		}
		return $parameters;
	}
	protected function parseAction($action)
	{
		if (is_callable($action))
		{
			return array('uses' => $action);
		}
		elseif ( ! isset($action['uses']))
		{
			$action['uses'] = $this->findCallable($action);
		}
		return $action;
	}
	protected function findCallable(array $action)
	{
		return array_first($action, function($key, $value)
		{
			return is_callable($value);
		});
	}
	public static function getValidators()
	{
		if (isset(static::$validators)) return static::$validators;
		return static::$validators = array(
			new MethodValidator, new SchemeValidator,
			new HostValidator, new UriValidator,
		);
	}
	public function before($filters)
	{
		return $this->addFilters('before', $filters);
	}
	public function after($filters)
	{
		return $this->addFilters('after', $filters);
	}
	protected function addFilters($type, $filters)
	{
		$filters = static::explodeFilters($filters);
		if (isset($this->action[$type]))
		{
			$existing = static::explodeFilters($this->action[$type]);
			$this->action[$type] = array_merge($existing, $filters);
		}
		else
		{
			$this->action[$type] = $filters;
		}
		return $this;
	}
	public function defaults($key, $value)
	{
		$this->defaults[$key] = $value;
		return $this;
	}
	public function where($name, $expression = null)
	{
		foreach ($this->parseWhere($name, $expression) as $name => $expression)
		{
			$this->wheres[$name] = $expression;
		}
		return $this;
	}
	protected function parseWhere($name, $expression)
	{
		return is_array($name) ? $name : array($name => $expression);
	}
	protected function whereArray(array $wheres)
	{
		foreach ($wheres as $name => $expression)
		{
			$this->where($name, $expression);
		}
		return $this;
	}
	public function prefix($prefix)
	{
		$this->uri = trim($prefix, '/').'/'.trim($this->uri, '/');
		return $this;
	}
	public function getPath()
	{
		return $this->uri();
	}
	public function uri()
	{
		return $this->uri;
	}
	public function getMethods()
	{
		return $this->methods();
	}
	public function methods()
	{
		return $this->methods;
	}
	public function httpOnly()
	{
		return in_array('http', $this->action, true);
	}
	public function httpsOnly()
	{
		return $this->secure();
	}
	public function secure()
	{
		return in_array('https', $this->action, true);
	}
	public function domain()
	{
		return isset($this->action['domain']) ? $this->action['domain'] : null;
	}
	public function getUri()
	{
		return $this->uri;
	}
	public function setUri($uri)
	{
		$this->uri = $uri;
		return $this;
	}
	public function getPrefix()
	{
		return isset($this->action['prefix']) ? $this->action['prefix'] : null;
	}
	public function getName()
	{
		return isset($this->action['as']) ? $this->action['as'] : null;
	}
	public function getActionName()
	{
		return isset($this->action['controller']) ? $this->action['controller'] : 'Closure';
	}
	public function getAction()
	{
		return $this->action;
	}
	public function setAction(array $action)
	{
		$this->action = $action;
		return $this;
	}
	public function getCompiled()
	{
		return $this->compiled;
	}
	public function setContainer(Container $container)
	{
		$this->container = $container;
		return $this;
	}
	public function prepareForSerialization()
	{
		if ($this->action['uses'] instanceof Closure)
		{
			throw new LogicException("Unable to prepare route [{$this->uri}] for serialization. Uses Closure.");
		}
		unset($this->container, $this->compiled);
	}
	public function __get($key)
	{
		return $this->parameter($key);
	}
}
