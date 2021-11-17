<?php namespace Illuminate\Cache;
use Illuminate\Contracts\Cache\Store;
class ArrayStore extends TaggableStore implements Store {
	protected $storage = array();
	public function get($key)
	{
		if (array_key_exists($key, $this->storage))
		{
			return $this->storage[$key];
		}
	}
	public function put($key, $value, $minutes)
	{
		$this->storage[$key] = $value;
	}
	public function increment($key, $value = 1)
	{
		$this->storage[$key] = $this->storage[$key] + $value;
		return $this->storage[$key];
	}
	public function decrement($key, $value = 1)
	{
		return $this->increment($key, $value * -1);
	}
	public function forever($key, $value)
	{
		$this->put($key, $value, 0);
	}
	public function forget($key)
	{
		unset($this->storage[$key]);
		return true;
	}
	public function flush()
	{
		$this->storage = array();
	}
	public function getPrefix()
	{
		return '';
	}
}
