<?php namespace Illuminate\Pagination;
use Closure;
use ArrayIterator;
abstract class AbstractPaginator {
	protected $items;
	protected $perPage;
	protected $currentPage;
	protected $path = '/';
	protected $query = [];
	protected $fragment = null;
	protected $pageName = 'page';
	protected static $currentPathResolver;
	protected static $currentPageResolver;
	protected static $presenterResolver;
	protected function isValidPageNumber($page)
	{
		return $page >= 1 && filter_var($page, FILTER_VALIDATE_INT) !== false;
	}
	public function getUrlRange($start, $end)
	{
		$urls = [];
		for ($page = $start; $page <= $end; $page++)
		{
			$urls[$page] = $this->url($page);
		}
		return $urls;
	}
	public function url($page)
	{
		if ($page <= 0) $page = 1;
		$parameters = [$this->pageName => $page];
		if (count($this->query) > 0)
		{
			$parameters = array_merge($this->query, $parameters);
		}
		return $this->path.'?'
		                .http_build_query($parameters, null, '&')
		                .$this->buildFragment();
	}
	public function previousPageUrl()
	{
		if ($this->currentPage() > 1)
		{
			return $this->url($this->currentPage() - 1);
		}
	}
	public function fragment($fragment = null)
	{
		if (is_null($fragment)) return $this->fragment;
		$this->fragment = $fragment;
		return $this;
	}
	public function appends($key, $value = null)
	{
		if (is_array($key)) return $this->appendArray($key);
		return $this->addQuery($key, $value);
	}
	protected function appendArray(array $keys)
	{
		foreach ($keys as $key => $value)
		{
			$this->addQuery($key, $value);
		}
		return $this;
	}
	public function addQuery($key, $value)
	{
		if ($key !== $this->pageName)
		{
			$this->query[$key] = $value;
		}
		return $this;
	}
	protected function buildFragment()
	{
		return $this->fragment ? '#'.$this->fragment : '';
	}
	public function items()
	{
		return $this->items->all();
	}
	public function firstItem()
	{
		return ($this->currentPage - 1) * $this->perPage + 1;
	}
	public function lastItem()
	{
		return $this->firstItem() + $this->count() - 1;
	}
	public function perPage()
	{
		return $this->perPage;
	}
	public function currentPage()
	{
		return $this->currentPage;
	}
	public function hasPages()
	{
		return ! ($this->currentPage() == 1 && ! $this->hasMorePages());
	}
	public static function resolveCurrentPath($default = '/')
	{
		if (isset(static::$currentPathResolver))
		{
			return call_user_func(static::$currentPathResolver);
		}
		return $default;
	}
	public static function currentPathResolver(Closure $resolver)
	{
		static::$currentPathResolver = $resolver;
	}
	public static function resolveCurrentPage($default = 1)
	{
		if (isset(static::$currentPageResolver))
		{
			return call_user_func(static::$currentPageResolver);
		}
		return $default;
	}
	public static function currentPageResolver(Closure $resolver)
	{
		static::$currentPageResolver = $resolver;
	}
	public static function presenter(Closure $resolver)
	{
		static::$presenterResolver = $resolver;
	}
	public function setPageName($name)
	{
		$this->pageName = $name;
		return $this;
	}
	public function setPath($path)
	{
		$this->path = $path;
		return $this;
	}
	public function getIterator()
	{
		return new ArrayIterator($this->items->all());
	}
	public function isEmpty()
	{
		return $this->items->isEmpty();
	}
	public function count()
	{
		return $this->items->count();
	}
	public function getCollection()
	{
		return $this->items;
	}
	public function offsetExists($key)
	{
		return $this->items->has($key);
	}
	public function offsetGet($key)
	{
		return $this->items->get($key);
	}
	public function offsetSet($key, $value)
	{
		$this->items->put($key, $value);
	}
	public function offsetUnset($key)
	{
		$this->items->forget($key);
	}
	public function __call($method, $parameters)
	{
		return call_user_func_array([$this->getCollection(), $method], $parameters);
	}
	public function __toString()
	{
		return $this->render();
	}
}
