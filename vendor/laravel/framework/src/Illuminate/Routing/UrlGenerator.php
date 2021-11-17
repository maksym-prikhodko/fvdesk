<?php namespace Illuminate\Routing;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Illuminate\Contracts\Routing\UrlRoutable;
use Illuminate\Contracts\Routing\UrlGenerator as UrlGeneratorContract;
class UrlGenerator implements UrlGeneratorContract {
	protected $routes;
	protected $request;
	protected $forcedRoot;
	protected $forceSchema;
	protected $cachedRoot;
	protected $cachedSchema;
	protected $rootNamespace;
	protected $sessionResolver;
	protected $dontEncode = array(
		'%2F' => '/',
		'%40' => '@',
		'%3A' => ':',
		'%3B' => ';',
		'%2C' => ',',
		'%3D' => '=',
		'%2B' => '+',
		'%21' => '!',
		'%2A' => '*',
		'%7C' => '|',
		'%3F' => '?',
		'%26' => '&',
		'%23' => '#',
		'%25' => '%',
	);
	public function __construct(RouteCollection $routes, Request $request)
	{
		$this->routes = $routes;
		$this->setRequest($request);
	}
	public function full()
	{
		return $this->request->fullUrl();
	}
	public function current()
	{
		return $this->to($this->request->getPathInfo());
	}
	public function previous()
	{
		$referrer = $this->request->headers->get('referer');
		$url = $referrer ? $this->to($referrer) : $this->getPreviousUrlFromSession();
		return $url ?: $this->to('/');
	}
	public function to($path, $extra = array(), $secure = null)
	{
		if ($this->isValidUrl($path)) return $path;
		$scheme = $this->getScheme($secure);
		$extra = $this->formatParameters($extra);
		$tail = implode('/', array_map(
			'rawurlencode', (array) $extra)
		);
		$root = $this->getRootUrl($scheme);
		return $this->trimUrl($root, $path, $tail);
	}
	public function secure($path, $parameters = array())
	{
		return $this->to($path, $parameters, true);
	}
	public function asset($path, $secure = null)
	{
		if ($this->isValidUrl($path)) return $path;
		$root = $this->getRootUrl($this->getScheme($secure));
		return $this->removeIndex($root).'/'.trim($path, '/');
	}
	protected function removeIndex($root)
	{
		$i = 'index.php';
		return str_contains($root, $i) ? str_replace('/'.$i, '', $root) : $root;
	}
	public function secureAsset($path)
	{
		return $this->asset($path, true);
	}
	protected function getScheme($secure)
	{
		if (is_null($secure))
		{
			if (is_null($this->cachedSchema))
			{
				$this->cachedSchema = $this->forceSchema ?: $this->request->getScheme().':
			}
			return $this->cachedSchema;
		}
		return $secure ? 'https:
	}
	public function forceSchema($schema)
	{
		$this->cachedSchema = null;
		$this->forceSchema = $schema.':
	}
	public function route($name, $parameters = array(), $absolute = true)
	{
		if ( ! is_null($route = $this->routes->getByName($name)))
		{
			return $this->toRoute($route, $parameters, $absolute);
		}
		throw new InvalidArgumentException("Route [{$name}] not defined.");
	}
	protected function toRoute($route, $parameters, $absolute)
	{
		$parameters = $this->formatParameters($parameters);
		$domain = $this->getRouteDomain($route, $parameters);
		$uri = strtr(rawurlencode($this->addQueryString($this->trimUrl(
			$root = $this->replaceRoot($route, $domain, $parameters),
			$this->replaceRouteParameters($route->uri(), $parameters)
		), $parameters)), $this->dontEncode);
		return $absolute ? $uri : '/'.ltrim(str_replace($root, '', $uri), '/');
	}
	protected function replaceRoot($route, $domain, &$parameters)
	{
		return $this->replaceRouteParameters($this->getRouteRoot($route, $domain), $parameters);
	}
	protected function replaceRouteParameters($path, array &$parameters)
	{
		if (count($parameters))
		{
			$path = preg_replace_sub(
				'/\{.*?\}/', $parameters, $this->replaceNamedParameters($path, $parameters)
			);
		}
		return trim(preg_replace('/\{.*?\?\}/', '', $path), '/');
	}
	protected function replaceNamedParameters($path, &$parameters)
	{
		return preg_replace_callback('/\{(.*?)\??\}/', function($m) use (&$parameters)
		{
			return isset($parameters[$m[1]]) ? array_pull($parameters, $m[1]) : $m[0];
		}, $path);
	}
	protected function addQueryString($uri, array $parameters)
	{
		if ( ! is_null($fragment = parse_url($uri, PHP_URL_FRAGMENT)))
		{
			$uri = preg_replace('/#.*/', '', $uri);
		}
		$uri .= $this->getRouteQueryString($parameters);
		return is_null($fragment) ? $uri : $uri."#{$fragment}";
	}
	protected function formatParameters($parameters)
	{
		return $this->replaceRoutableParameters($parameters);
	}
	protected function replaceRoutableParameters($parameters = array())
	{
		$parameters = is_array($parameters) ? $parameters : array($parameters);
		foreach ($parameters as $key => $parameter)
		{
			if ($parameter instanceof UrlRoutable)
			{
				$parameters[$key] = $parameter->getRouteKey();
			}
		}
		return $parameters;
	}
	protected function getRouteQueryString(array $parameters)
	{
		if (count($parameters) == 0) return '';
		$query = http_build_query(
			$keyed = $this->getStringParameters($parameters)
		);
		if (count($keyed) < count($parameters))
		{
			$query .= '&'.implode(
				'&', $this->getNumericParameters($parameters)
			);
		}
		return '?'.trim($query, '&');
	}
	protected function getStringParameters(array $parameters)
	{
		return array_where($parameters, function($k, $v) { return is_string($k); });
	}
	protected function getNumericParameters(array $parameters)
	{
		return array_where($parameters, function($k, $v) { return is_numeric($k); });
	}
	protected function getRouteDomain($route, &$parameters)
	{
		return $route->domain() ? $this->formatDomain($route, $parameters) : null;
	}
	protected function formatDomain($route, &$parameters)
	{
		return $this->addPortToDomain($this->getDomainAndScheme($route));
	}
	protected function getDomainAndScheme($route)
	{
		return $this->getRouteScheme($route).$route->domain();
	}
	protected function addPortToDomain($domain)
	{
		if (in_array($this->request->getPort(), array('80', '443')))
		{
			return $domain;
		}
		return $domain.':'.$this->request->getPort();
	}
	protected function getRouteRoot($route, $domain)
	{
		return $this->getRootUrl($this->getRouteScheme($route), $domain);
	}
	protected function getRouteScheme($route)
	{
		if ($route->httpOnly())
		{
			return $this->getScheme(false);
		}
		elseif ($route->httpsOnly())
		{
			return $this->getScheme(true);
		}
		return $this->getScheme(null);
	}
	public function action($action, $parameters = array(), $absolute = true)
	{
		if ($this->rootNamespace && ! (strpos($action, '\\') === 0))
		{
			$action = $this->rootNamespace.'\\'.$action;
		}
		else
		{
			$action = trim($action, '\\');
		}
		if ( ! is_null($route = $this->routes->getByAction($action)))
		{
			 return $this->toRoute($route, $parameters, $absolute);
		}
		throw new InvalidArgumentException("Action {$action} not defined.");
	}
	protected function getRootUrl($scheme, $root = null)
	{
		if (is_null($root))
		{
			if (is_null($this->cachedRoot))
			{
				$this->cachedRoot = $this->forcedRoot ?: $this->request->root();
			}
			$root = $this->cachedRoot;
		}
		$start = starts_with($root, 'http:
		return preg_replace('~'.$start.'~', $scheme, $root, 1);
	}
	public function forceRootUrl($root)
	{
		$this->forcedRoot = rtrim($root, '/');
		$this->cachedRoot = null;
	}
	public function isValidUrl($path)
	{
		if (starts_with($path, ['#', '
		return filter_var($path, FILTER_VALIDATE_URL) !== false;
	}
	protected function trimUrl($root, $path, $tail = '')
	{
		return trim($root.'/'.trim($path.'/'.$tail, '/'), '/');
	}
	public function getRequest()
	{
		return $this->request;
	}
	public function setRequest(Request $request)
	{
		$this->request = $request;
		$this->cachedRoot = null;
		$this->cachedSchema = null;
	}
	public function setRoutes(RouteCollection $routes)
	{
		$this->routes = $routes;
		return $this;
	}
	protected function getPreviousUrlFromSession()
	{
		$session = $this->getSession();
		return $session ? $session->previousUrl() : null;
	}
	protected function getSession()
	{
		return call_user_func($this->sessionResolver ?: function() {});
	}
	public function setSessionResolver(callable $sessionResolver)
	{
		$this->sessionResolver = $sessionResolver;
		return $this;
	}
	public function setRootControllerNamespace($rootNamespace)
	{
		$this->rootNamespace = $rootNamespace;
		return $this;
	}
}
