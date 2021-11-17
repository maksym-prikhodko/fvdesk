<?php namespace Illuminate\Cache;
use Illuminate\Contracts\Cache\Store;
class MemcachedStore extends TaggableStore implements Store {
	protected $memcached;
	protected $prefix;
	public function __construct($memcached, $prefix = '')
	{
		$this->memcached = $memcached;
		$this->prefix = strlen($prefix) > 0 ? $prefix.':' : '';
	}
	public function get($key)
	{
		$value = $this->memcached->get($this->prefix.$key);
		if ($this->memcached->getResultCode() == 0)
		{
			return $value;
		}
	}
	public function put($key, $value, $minutes)
	{
		$this->memcached->set($this->prefix.$key, $value, $minutes * 60);
	}
	public function add($key, $value, $minutes)
	{
		return $this->memcached->add($this->prefix.$key, $value, $minutes * 60);
	}
	public function increment($key, $value = 1)
	{
		return $this->memcached->increment($this->prefix.$key, $value);
	}
	public function decrement($key, $value = 1)
	{
		return $this->memcached->decrement($this->prefix.$key, $value);
	}
	public function forever($key, $value)
	{
		$this->put($key, $value, 0);
	}
	public function forget($key)
	{
		return $this->memcached->delete($this->prefix.$key);
	}
	public function flush()
	{
		$this->memcached->flush();
	}
	public function getMemcached()
	{
		return $this->memcached;
	}
	public function getPrefix()
	{
		return $this->prefix;
	}
}
