<?php namespace Illuminate\Config;
use ArrayAccess;
use Illuminate\Contracts\Config\Repository as ConfigContract;
class Repository implements ArrayAccess, ConfigContract {
	protected $items = [];
	public function __construct(array $items = array())
	{
		$this->items = $items;
	}
	public function has($key)
	{
		return array_has($this->items, $key);
	}
	public function get($key, $default = null)
	{
		return array_get($this->items, $key, $default);
	}
	public function set($key, $value = null)
	{
		if (is_array($key))
		{
			foreach ($key as $innerKey => $innerValue)
			{
				array_set($this->items, $innerKey, $innerValue);
			}
		}
		else
		{
			array_set($this->items, $key, $value);
		}
	}
	public function prepend($key, $value)
	{
		$array = $this->get($key);
		array_unshift($array, $value);
		$this->set($key, $array);
	}
	public function push($key, $value)
	{
		$array = $this->get($key);
		$array[] = $value;
		$this->set($key, $array);
	}
	public function all()
	{
		return $this->items;
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
		$this->set($key, $value);
	}
	public function offsetUnset($key)
	{
		$this->set($key, null);
	}
}
