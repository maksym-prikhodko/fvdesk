<?php namespace Illuminate\Support;
use Closure;
use Countable;
use ArrayAccess;
use ArrayIterator;
use CachingIterator;
use JsonSerializable;
use IteratorAggregate;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Arrayable;
class Collection implements ArrayAccess, Arrayable, Countable, IteratorAggregate, Jsonable, JsonSerializable {
	protected $items = array();
	public function __construct($items = array())
	{
		$items = is_null($items) ? [] : $this->getArrayableItems($items);
		$this->items = (array) $items;
	}
	public static function make($items = null)
	{
		return new static($items);
	}
	public function all()
	{
		return $this->items;
	}
	public function collapse()
	{
		return new static(array_collapse($this->items));
	}
	public function contains($key, $value = null)
	{
		if (func_num_args() == 2)
		{
			return $this->contains(function($k, $item) use ($key, $value)
			{
				return data_get($item, $key) == $value;
			});
		}
		if ($this->useAsCallable($key))
		{
			return ! is_null($this->first($key));
		}
		return in_array($key, $this->items);
	}
	public function diff($items)
	{
		return new static(array_diff($this->items, $this->getArrayableItems($items)));
	}
	public function each(callable $callback)
	{
		array_map($callback, $this->items);
		return $this;
	}
	public function fetch($key)
	{
		return new static(array_fetch($this->items, $key));
	}
	public function filter(callable $callback)
	{
		return new static(array_filter($this->items, $callback));
	}
	public function where($key, $value, $strict = true)
	{
		return $this->filter(function($item) use ($key, $value, $strict)
		{
			return $strict ? data_get($item, $key) === $value
                           : data_get($item, $key) == $value;
		});
	}
	public function whereLoose($key, $value)
	{
		return $this->where($key, $value, false);
	}
	public function first(callable $callback = null, $default = null)
	{
		if (is_null($callback))
		{
			return count($this->items) > 0 ? reset($this->items) : null;
		}
		return array_first($this->items, $callback, $default);
	}
	public function flatten()
	{
		return new static(array_flatten($this->items));
	}
	public function flip()
	{
		return new static(array_flip($this->items));
	}
	public function forget($key)
	{
		$this->offsetUnset($key);
	}
	public function get($key, $default = null)
	{
		if ($this->offsetExists($key))
		{
			return $this->items[$key];
		}
		return value($default);
	}
	public function groupBy($groupBy)
	{
		if ( ! $this->useAsCallable($groupBy))
		{
			return $this->groupBy($this->valueRetriever($groupBy));
		}
		$results = [];
		foreach ($this->items as $key => $value)
		{
			$results[$groupBy($value, $key)][] = $value;
		}
		return new static($results);
	}
	public function keyBy($keyBy)
	{
		if ( ! $this->useAsCallable($keyBy))
		{
			return $this->keyBy($this->valueRetriever($keyBy));
		}
		$results = [];
		foreach ($this->items as $item)
		{
			$results[$keyBy($item)] = $item;
		}
		return new static($results);
	}
	public function has($key)
	{
		return $this->offsetExists($key);
	}
	public function implode($value, $glue = null)
	{
		$first = $this->first();
		if (is_array($first) || is_object($first))
		{
			return implode($glue, $this->lists($value));
		}
		return implode($value, $this->items);
	}
	public function intersect($items)
	{
		return new static(array_intersect($this->items, $this->getArrayableItems($items)));
	}
	public function isEmpty()
	{
		return empty($this->items);
	}
	protected function useAsCallable($value)
	{
		return ! is_string($value) && is_callable($value);
	}
	public function keys()
	{
		return new static(array_keys($this->items));
	}
	public function last()
	{
		return count($this->items) > 0 ? end($this->items) : null;
	}
	public function lists($value, $key = null)
	{
		return array_pluck($this->items, $value, $key);
	}
	public function map(callable $callback)
	{
		return new static(array_map($callback, $this->items, array_keys($this->items)));
	}
	public function merge($items)
	{
		return new static(array_merge($this->items, $this->getArrayableItems($items)));
	}
	public function forPage($page, $perPage)
	{
		return $this->slice(($page - 1) * $perPage, $perPage);
	}
	public function pop()
	{
		return array_pop($this->items);
	}
	public function prepend($value)
	{
		array_unshift($this->items, $value);
	}
	public function push($value)
	{
		$this->offsetSet(null, $value);
	}
	public function pull($key, $default = null)
	{
		return array_pull($this->items, $key, $default);
	}
	public function put($key, $value)
	{
		$this->offsetSet($key, $value);
	}
	public function random($amount = 1)
	{
		if ($this->isEmpty()) return;
		$keys = array_rand($this->items, $amount);
		return is_array($keys) ? array_intersect_key($this->items, array_flip($keys)) : $this->items[$keys];
	}
	public function reduce(callable $callback, $initial = null)
	{
		return array_reduce($this->items, $callback, $initial);
	}
	public function reject($callback)
	{
		if ($this->useAsCallable($callback))
		{
			return $this->filter(function($item) use ($callback)
			{
				return ! $callback($item);
			});
		}
		return $this->filter(function($item) use ($callback)
		{
			return $item != $callback;
		});
	}
	public function reverse()
	{
		return new static(array_reverse($this->items));
	}
	public function search($value, $strict = false)
	{
		if ( ! $this->useAsCallable($value))
		{
			return array_search($value, $this->items, $strict);
		}
		foreach ($this->items as $key => $item)
		{
			if ($value($item, $key)) return $key;
		}
		return false;
	}
	public function shift()
	{
		return array_shift($this->items);
	}
	public function shuffle()
	{
		shuffle($this->items);
		return $this;
	}
	public function slice($offset, $length = null, $preserveKeys = false)
	{
		return new static(array_slice($this->items, $offset, $length, $preserveKeys));
	}
	public function chunk($size, $preserveKeys = false)
	{
		$chunks = [];
		foreach (array_chunk($this->items, $size, $preserveKeys) as $chunk)
		{
			$chunks[] = new static($chunk);
		}
		return new static($chunks);
	}
	public function sort(callable $callback)
	{
		uasort($this->items, $callback);
		return $this;
	}
	public function sortBy($callback, $options = SORT_REGULAR, $descending = false)
	{
		$results = [];
		if ( ! $this->useAsCallable($callback))
		{
			$callback = $this->valueRetriever($callback);
		}
		foreach ($this->items as $key => $value)
		{
			$results[$key] = $callback($value, $key);
		}
		$descending ? arsort($results, $options)
                    : asort($results, $options);
		foreach (array_keys($results) as $key)
		{
			$results[$key] = $this->items[$key];
		}
		$this->items = $results;
		return $this;
	}
	public function sortByDesc($callback, $options = SORT_REGULAR)
	{
		return $this->sortBy($callback, $options, true);
	}
	public function splice($offset, $length = 0, $replacement = [])
	{
		return new static(array_splice($this->items, $offset, $length, $replacement));
	}
	public function sum($callback = null)
	{
		if (is_null($callback))
		{
			return array_sum($this->items);
		}
		if ( ! $this->useAsCallable($callback))
		{
			$callback = $this->valueRetriever($callback);
		}
		return $this->reduce(function($result, $item) use ($callback)
		{
			return $result += $callback($item);
		}, 0);
	}
	public function take($limit = null)
	{
		if ($limit < 0) return $this->slice($limit, abs($limit));
		return $this->slice(0, $limit);
	}
	public function transform(callable $callback)
	{
		$this->items = array_map($callback, $this->items);
		return $this;
	}
	public function unique()
	{
		return new static(array_unique($this->items));
	}
	public function values()
	{
		return new static(array_values($this->items));
	}
	protected function valueRetriever($value)
	{
		return function($item) use ($value)
		{
			return data_get($item, $value);
		};
	}
	public function toArray()
	{
		return array_map(function($value)
		{
			return $value instanceof Arrayable ? $value->toArray() : $value;
		}, $this->items);
	}
	public function jsonSerialize()
	{
		return $this->toArray();
	}
	public function toJson($options = 0)
	{
		return json_encode($this->toArray(), $options);
	}
	public function getIterator()
	{
		return new ArrayIterator($this->items);
	}
	public function getCachingIterator($flags = CachingIterator::CALL_TOSTRING)
	{
		return new CachingIterator($this->getIterator(), $flags);
	}
	public function count()
	{
		return count($this->items);
	}
	public function offsetExists($key)
	{
		return array_key_exists($key, $this->items);
	}
	public function offsetGet($key)
	{
		return $this->items[$key];
	}
	public function offsetSet($key, $value)
	{
		if (is_null($key))
		{
			$this->items[] = $value;
		}
		else
		{
			$this->items[$key] = $value;
		}
	}
	public function offsetUnset($key)
	{
		unset($this->items[$key]);
	}
	public function __toString()
	{
		return $this->toJson();
	}
	protected function getArrayableItems($items)
	{
		if ($items instanceof Collection)
		{
			$items = $items->all();
		}
		elseif ($items instanceof Arrayable)
		{
			$items = $items->toArray();
		}
		return $items;
	}
}
