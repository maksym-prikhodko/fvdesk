<?php namespace Illuminate\Cache;
use Closure;
use DateTime;
use Carbon\Carbon;
use Illuminate\Contracts\Cache\Store;
class TaggedCache implements Store {
	protected $store;
	protected $tags;
	public function __construct(Store $store, TagSet $tags)
	{
		$this->tags = $tags;
		$this->store = $store;
	}
	public function has($key)
	{
		return ! is_null($this->get($key));
	}
	public function get($key, $default = null)
	{
		$value = $this->store->get($this->taggedItemKey($key));
		return ! is_null($value) ? $value : value($default);
	}
	public function put($key, $value, $minutes)
	{
		$minutes = $this->getMinutes($minutes);
		if ( ! is_null($minutes))
		{
			$this->store->put($this->taggedItemKey($key), $value, $minutes);
		}
	}
	public function add($key, $value, $minutes)
	{
		if (is_null($this->get($key)))
		{
			$this->put($key, $value, $minutes);
			return true;
		}
		return false;
	}
	public function increment($key, $value = 1)
	{
		$this->store->increment($this->taggedItemKey($key), $value);
	}
	public function decrement($key, $value = 1)
	{
		$this->store->decrement($this->taggedItemKey($key), $value);
	}
	public function forever($key, $value)
	{
		$this->store->forever($this->taggedItemKey($key), $value);
	}
	public function forget($key)
	{
		return $this->store->forget($this->taggedItemKey($key));
	}
	public function flush()
	{
		$this->tags->reset();
	}
	public function remember($key, $minutes, Closure $callback)
	{
		if ( ! is_null($value = $this->get($key))) return $value;
		$this->put($key, $value = $callback(), $minutes);
		return $value;
	}
	public function sear($key, Closure $callback)
	{
		return $this->rememberForever($key, $callback);
	}
	public function rememberForever($key, Closure $callback)
	{
		if ( ! is_null($value = $this->get($key))) return $value;
		$this->forever($key, $value = $callback());
		return $value;
	}
	public function taggedItemKey($key)
	{
		return sha1($this->tags->getNamespace()).':'.$key;
	}
	public function getPrefix()
	{
		return $this->store->getPrefix();
	}
	protected function getMinutes($duration)
	{
		if ($duration instanceof DateTime)
		{
			$fromNow = Carbon::instance($duration)->diffInMinutes();
			return $fromNow > 0 ? $fromNow : null;
		}
		return is_string($duration) ? (int) $duration : $duration;
	}
}
