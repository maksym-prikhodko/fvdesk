<?php namespace Illuminate\Http;
use Closure;
use ArrayAccess;
use SplFileInfo;
use RuntimeException;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
class Request extends SymfonyRequest implements ArrayAccess {
	protected $json;
	protected $sessionStore;
	protected $userResolver;
	protected $routeResolver;
	public static function capture()
	{
		static::enableHttpMethodParameterOverride();
		return static::createFromBase(SymfonyRequest::createFromGlobals());
	}
	public function instance()
	{
		return $this;
	}
	public function method()
	{
		return $this->getMethod();
	}
	public function root()
	{
		return rtrim($this->getSchemeAndHttpHost().$this->getBaseUrl(), '/');
	}
	public function url()
	{
		return rtrim(preg_replace('/\?.*/', '', $this->getUri()), '/');
	}
	public function fullUrl()
	{
		$query = $this->getQueryString();
		return $query ? $this->url().'?'.$query : $this->url();
	}
	public function path()
	{
		$pattern = trim($this->getPathInfo(), '/');
		return $pattern == '' ? '/' : $pattern;
	}
	public function decodedPath()
	{
		return rawurldecode($this->path());
	}
	public function segment($index, $default = null)
	{
		return array_get($this->segments(), $index - 1, $default);
	}
	public function segments()
	{
		$segments = explode('/', $this->path());
		return array_values(array_filter($segments, function($v) { return $v != ''; }));
	}
	public function is()
	{
		foreach (func_get_args() as $pattern)
		{
			if (str_is($pattern, urldecode($this->path())))
			{
				return true;
			}
		}
		return false;
	}
	public function ajax()
	{
		return $this->isXmlHttpRequest();
	}
	public function pjax()
	{
		return $this->headers->get('X-PJAX') == true;
	}
	public function secure()
	{
		return $this->isSecure();
	}
	public function ip()
	{
		return $this->getClientIp();
	}
	public function ips()
	{
		return $this->getClientIps();
	}
	public function exists($key)
	{
		$keys = is_array($key) ? $key : func_get_args();
		$input = $this->all();
		foreach ($keys as $value)
		{
			if ( ! array_key_exists($value, $input)) return false;
		}
		return true;
	}
	public function has($key)
	{
		$keys = is_array($key) ? $key : func_get_args();
		foreach ($keys as $value)
		{
			if ($this->isEmptyString($value)) return false;
		}
		return true;
	}
	protected function isEmptyString($key)
	{
		$boolOrArray = is_bool($this->input($key)) || is_array($this->input($key));
		return ! $boolOrArray && trim((string) $this->input($key)) === '';
	}
	public function all()
	{
		return array_replace_recursive($this->input(), $this->files->all());
	}
	public function input($key = null, $default = null)
	{
		$input = $this->getInputSource()->all() + $this->query->all();
		return array_get($input, $key, $default);
	}
	public function only($keys)
	{
		$keys = is_array($keys) ? $keys : func_get_args();
		$results = [];
		$input = $this->all();
		foreach ($keys as $key)
		{
			array_set($results, $key, array_get($input, $key));
		}
		return $results;
	}
	public function except($keys)
	{
		$keys = is_array($keys) ? $keys : func_get_args();
		$results = $this->all();
		array_forget($results, $keys);
		return $results;
	}
	public function query($key = null, $default = null)
	{
		return $this->retrieveItem('query', $key, $default);
	}
	public function hasCookie($key)
	{
		return ! is_null($this->cookie($key));
	}
	public function cookie($key = null, $default = null)
	{
		return $this->retrieveItem('cookies', $key, $default);
	}
	public function file($key = null, $default = null)
	{
		return array_get($this->files->all(), $key, $default);
	}
	public function hasFile($key)
	{
		if ( ! is_array($files = $this->file($key))) $files = array($files);
		foreach ($files as $file)
		{
			if ($this->isValidFile($file)) return true;
		}
		return false;
	}
	protected function isValidFile($file)
	{
		return $file instanceof SplFileInfo && $file->getPath() != '';
	}
	public function header($key = null, $default = null)
	{
		return $this->retrieveItem('headers', $key, $default);
	}
	public function server($key = null, $default = null)
	{
		return $this->retrieveItem('server', $key, $default);
	}
	public function old($key = null, $default = null)
	{
		return $this->session()->getOldInput($key, $default);
	}
	public function flash($filter = null, $keys = array())
	{
		$flash = ( ! is_null($filter)) ? $this->$filter($keys) : $this->input();
		$this->session()->flashInput($flash);
	}
	public function flashOnly($keys)
	{
		$keys = is_array($keys) ? $keys : func_get_args();
		return $this->flash('only', $keys);
	}
	public function flashExcept($keys)
	{
		$keys = is_array($keys) ? $keys : func_get_args();
		return $this->flash('except', $keys);
	}
	public function flush()
	{
		$this->session()->flashInput(array());
	}
	protected function retrieveItem($source, $key, $default)
	{
		if (is_null($key))
		{
			return $this->$source->all();
		}
		return $this->$source->get($key, $default, true);
	}
	public function merge(array $input)
	{
		$this->getInputSource()->add($input);
	}
	public function replace(array $input)
	{
		$this->getInputSource()->replace($input);
	}
	public function json($key = null, $default = null)
	{
		if ( ! isset($this->json))
		{
			$this->json = new ParameterBag((array) json_decode($this->getContent(), true));
		}
		if (is_null($key)) return $this->json;
		return array_get($this->json->all(), $key, $default);
	}
	protected function getInputSource()
	{
		if ($this->isJson()) return $this->json();
		return $this->getMethod() == 'GET' ? $this->query : $this->request;
	}
	public function isJson()
	{
		return str_contains($this->header('CONTENT_TYPE'), '/json');
	}
	public function wantsJson()
	{
		$acceptable = $this->getAcceptableContentTypes();
		return isset($acceptable[0]) && $acceptable[0] == 'application/json';
	}
	public function format($default = 'html')
	{
		foreach ($this->getAcceptableContentTypes() as $type)
		{
			if ($format = $this->getFormat($type)) return $format;
		}
		return $default;
	}
	public static function createFromBase(SymfonyRequest $request)
	{
		if ($request instanceof static) return $request;
		$content = $request->content;
		$request = (new static)->duplicate(
			$request->query->all(), $request->request->all(), $request->attributes->all(),
			$request->cookies->all(), $request->files->all(), $request->server->all()
		);
		$request->content = $content;
		$request->request = $request->getInputSource();
		return $request;
	}
	public function duplicate(array $query = null, array $request = null, array $attributes = null, array $cookies = null, array $files = null, array $server = null)
	{
		return parent::duplicate($query, $request, $attributes, $cookies, array_filter((array) $files), $server);
	}
	public function session()
	{
		if ( ! $this->hasSession())
		{
			throw new RuntimeException("Session store not set on request.");
		}
		return $this->getSession();
	}
	public function user()
	{
		return call_user_func($this->getUserResolver());
	}
	public function route()
	{
		if (func_num_args() == 1)
		{
			return $this->route()->parameter(func_get_arg(0));
		}
		else
		{
			return call_user_func($this->getRouteResolver());
		}
	}
	public function getUserResolver()
	{
		return $this->userResolver ?: function() {};
	}
	public function setUserResolver(Closure $callback)
	{
		$this->userResolver = $callback;
		return $this;
	}
	public function getRouteResolver()
	{
		return $this->routeResolver ?: function() {};
	}
	public function setRouteResolver(Closure $callback)
	{
		$this->routeResolver = $callback;
		return $this;
	}
	public function offsetExists($offset)
	{
		return array_key_exists($offset, $this->all());
	}
	public function offsetGet($offset)
	{
		return $this->input($offset);
	}
	public function offsetSet($offset, $value)
	{
		return $this->getInputSource()->set($offset, $value);
	}
	public function offsetUnset($offset)
	{
		return $this->getInputSource()->remove($offset);
	}
	public function __get($key)
	{
		$input = $this->input();
		if (array_key_exists($key, $input))
		{
			return $this->input($key);
		}
		elseif ( ! is_null($this->route()))
		{
			return $this->route()->parameter($key);
		}
	}
}
