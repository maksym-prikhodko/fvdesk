<?php namespace Illuminate\Support;
use Illuminate\Support\Traits\Macroable;
class Arr {
	use Macroable;
	public static function add($array, $key, $value)
	{
		if (is_null(static::get($array, $key)))
		{
			static::set($array, $key, $value);
		}
		return $array;
	}
	public static function build($array, callable $callback)
	{
		$results = [];
		foreach ($array as $key => $value)
		{
			list($innerKey, $innerValue) = call_user_func($callback, $key, $value);
			$results[$innerKey] = $innerValue;
		}
		return $results;
	}
	public static function collapse($array)
	{
		$results = [];
		foreach ($array as $values)
		{
			if ($values instanceof Collection) $values = $values->all();
			$results = array_merge($results, $values);
		}
		return $results;
	}
	public static function divide($array)
	{
		return [array_keys($array), array_values($array)];
	}
	public static function dot($array, $prepend = '')
	{
		$results = [];
		foreach ($array as $key => $value)
		{
			if (is_array($value))
			{
				$results = array_merge($results, static::dot($value, $prepend.$key.'.'));
			}
			else
			{
				$results[$prepend.$key] = $value;
			}
		}
		return $results;
	}
	public static function except($array, $keys)
	{
		foreach ((array) $keys as $key)
		{
			static::forget($array, $key);
		}
		return $array;
	}
	public static function fetch($array, $key)
	{
		foreach (explode('.', $key) as $segment)
		{
			$results = [];
			foreach ($array as $value)
			{
				if (array_key_exists($segment, $value = (array) $value))
				{
					$results[] = $value[$segment];
				}
			}
			$array = array_values($results);
		}
		return array_values($results);
	}
	public static function first($array, callable $callback, $default = null)
	{
		foreach ($array as $key => $value)
		{
			if (call_user_func($callback, $key, $value)) return $value;
		}
		return value($default);
	}
	public static function last($array, callable $callback, $default = null)
	{
		return static::first(array_reverse($array), $callback, $default);
	}
	public static function flatten($array)
	{
		$return = [];
		array_walk_recursive($array, function($x) use (&$return) { $return[] = $x; });
		return $return;
	}
	public static function forget(&$array, $keys)
	{
		$original =& $array;
		foreach ((array) $keys as $key)
		{
			$parts = explode('.', $key);
			while (count($parts) > 1)
			{
				$part = array_shift($parts);
				if (isset($array[$part]) && is_array($array[$part]))
				{
					$array =& $array[$part];
				}
			}
			unset($array[array_shift($parts)]);
			$array =& $original;
		}
	}
	public static function get($array, $key, $default = null)
	{
		if (is_null($key)) return $array;
		if (isset($array[$key])) return $array[$key];
		foreach (explode('.', $key) as $segment)
		{
			if ( ! is_array($array) || ! array_key_exists($segment, $array))
			{
				return value($default);
			}
			$array = $array[$segment];
		}
		return $array;
	}
	public static function has($array, $key)
	{
		if (empty($array) || is_null($key)) return false;
		if (array_key_exists($key, $array)) return true;
		foreach (explode('.', $key) as $segment)
		{
			if ( ! is_array($array) || ! array_key_exists($segment, $array))
			{
				return false;
			}
			$array = $array[$segment];
		}
		return true;
	}
	public static function only($array, $keys)
	{
		return array_intersect_key($array, array_flip((array) $keys));
	}
	public static function pluck($array, $value, $key = null)
	{
		$results = [];
		foreach ($array as $item)
		{
			$itemValue = data_get($item, $value);
			if (is_null($key))
			{
				$results[] = $itemValue;
			}
			else
			{
				$itemKey = data_get($item, $key);
				$results[$itemKey] = $itemValue;
			}
		}
		return $results;
	}
	public static function pull(&$array, $key, $default = null)
	{
		$value = static::get($array, $key, $default);
		static::forget($array, $key);
		return $value;
	}
	public static function set(&$array, $key, $value)
	{
		if (is_null($key)) return $array = $value;
		$keys = explode('.', $key);
		while (count($keys) > 1)
		{
			$key = array_shift($keys);
			if ( ! isset($array[$key]) || ! is_array($array[$key]))
			{
				$array[$key] = [];
			}
			$array =& $array[$key];
		}
		$array[array_shift($keys)] = $value;
		return $array;
	}
	public static function sort($array, callable $callback)
	{
		return Collection::make($array)->sortBy($callback)->all();
	}
	public static function where($array, callable $callback)
	{
		$filtered = [];
		foreach ($array as $key => $value)
		{
			if (call_user_func($callback, $key, $value)) $filtered[$key] = $value;
		}
		return $filtered;
	}
}
