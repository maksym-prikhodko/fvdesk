<?php namespace Illuminate\Cache;
use Illuminate\Contracts\Cache\Store;
class WinCacheStore extends TaggableStore implements Store {
	protected $prefix;
	public function __construct($prefix = '')
	{
		$this->prefix = $prefix;
	}
	public function get($key)
	{
		$value = wincache_ucache_get($this->prefix.$key);
		if ($value !== false)
		{
			return $value;
		}
	}
	public function put($key, $value, $minutes)
	{
		wincache_ucache_set($this->prefix.$key, $value, $minutes * 60);
	}
	public function increment($key, $value = 1)
	{
		return wincache_ucache_inc($this->prefix.$key, $value);
	}
	public function decrement($key, $value = 1)
	{
		return wincache_ucache_dec($this->prefix.$key, $value);
	}
	public function forever($key, $value)
	{
		$this->put($key, $value, 0);
	}
	public function forget($key)
	{
		return wincache_ucache_delete($this->prefix.$key);
	}
	public function flush()
	{
		wincache_ucache_clear();
	}
	public function getPrefix()
	{
		return $this->prefix;
	}
}
