<?php namespace Illuminate\Cache;
use Closure;
use DateTime;
use ArrayAccess;
use Carbon\Carbon;
use Illuminate\Contracts\Cache\Store;
use Illuminate\Support\Traits\Macroable;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Cache\Repository as CacheContract;
class Repository implements CacheContract, ArrayAccess {
	use Macroable {
		__call as macroCall;
	}
	protected $store;
	protected $events;
	protected $default = 60;
	public function __construct(Store $store)
	{
		$this->store = $store;
	}
	public function setEventDispatcher(Dispatcher $events)
	{
		$this->events = $events;
	}
	protected function fireCacheEvent($event, $payload)
	{
		if (isset($this->events))
		{
			$this->events->fire('cache.'.$event, $payload);
		}
	}
	public function has($key)
	{
		return ! is_null($this->get($key));
	}
	public function get($key, $default = null)
	{
		$value = $this->store->get($key);
		if (is_null($value))
		{
			$this->fireCacheEvent('missed', [$key]);
			$value = value($default);
		}
		else
		{
			$this->fireCacheEvent('hit', [$key, $value]);
		}
		return $value;
	}
	public function pull($key, $default = null)
	{
		$value = $this->get($key, $default);
		$this->forget($key);
		return $value;
	}
	public function put($key, $value, $minutes)
	{
		$minutes = $this->getMinutes($minutes);
		if ( ! is_null($minutes))
		{
			$this->store->put($key, $value, $minutes);
			$this->fireCacheEvent('write', [$key, $value, $minutes]);
		}
	}
	public function add($key, $value, $minutes)
	{
		if (method_exists($this->store, 'add'))
		{
			return $this->store->add($key, $value, $minutes);
		}
		if (is_null($this->get($key)))
		{
			$this->put($key, $value, $minutes);
			return true;
		}
		return false;
	}
	public function forever($key, $value)
	{
		$this->store->forever($key, $value);
		$this->fireCacheEvent('write', [$key, $value, 0]);
	}
	public function remember($key, $minutes, Closure $callback)
	{
		if ( ! is_null($value = $this->get($key)))
		{
			return $value;
		}
		$this->put($key, $value = $callback(), $minutes);
		return $value;
	}
	public function sear($key, Closure $callback)
	{
		return $this->rememberForever($key, $callback);
	}
	public function rememberForever($key, Closure $callback)
	{
		if ( ! is_null($value = $this->get($key)))
		{
			return $value;
		}
		$this->forever($key, $value = $callback());
		return $value;
	}
	public function forget($key)
	{
		$success = $this->store->forget($key);
		$this->fireCacheEvent('delete', [$key]);
		return $success;
	}
	public function getDefaultCacheTime()
	{
		return $this->default;
	}
	public function setDefaultCacheTime($minutes)
	{
		$this->default = $minutes;
	}
	public function getStore()
	{
		return $this->store;
	}
	public function offsetExists($key)
	{
		return $this->has($key);
	}
	public function offsetGet($key)
	{
		return $this->get($key);
	}
	public function offsetSet($key, $value)
	{
		$this->put($key, $value, $this->default);
	}
	public function offsetUnset($key)
	{
		$this->forget($key);
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
	public function __call($method, $parameters)
	{
		if (static::hasMacro($method))
		{
			return $this->macroCall($method, $parameters);
		}
		return call_user_func_array(array($this->store, $method), $parameters);
	}
}
