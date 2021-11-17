<?php namespace Illuminate\Cache;
use Illuminate\Contracts\Cache\Store;
class XCacheStore extends TaggableStore implements Store {
	protected $prefix;
	public function __construct($prefix = '')
	{
		$this->prefix = $prefix;
	}
	public function get($key)
	{
		$value = xcache_get($this->prefix.$key);
		if (isset($value))
		{
			return $value;
		}
	}
	public function put($key, $value, $minutes)
	{
		xcache_set($this->prefix.$key, $value, $minutes * 60);
	}
	public function increment($key, $value = 1)
	{
		return xcache_inc($this->prefix.$key, $value);
	}
	public function decrement($key, $value = 1)
	{
		return xcache_dec($this->prefix.$key, $value);
	}
	public function forever($key, $value)
	{
		return $this->put($key, $value, 0);
	}
	public function forget($key)
	{
		return xcache_unset($this->prefix.$key);
	}
	public function flush()
	{
		xcache_clear_cache(XC_TYPE_VAR);
	}
	public function getPrefix()
	{
		return $this->prefix;
	}
}
