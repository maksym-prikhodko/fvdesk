<?php namespace Illuminate\Database\Eloquent;
use Illuminate\Support\Collection as BaseCollection;
class Collection extends BaseCollection {
	public function find($key, $default = null)
	{
		if ($key instanceof Model)
		{
			$key = $key->getKey();
		}
		return array_first($this->items, function($itemKey, $model) use ($key)
		{
			return $model->getKey() == $key;
		}, $default);
	}
	public function load($relations)
	{
		if (count($this->items) > 0)
		{
			if (is_string($relations)) $relations = func_get_args();
			$query = $this->first()->newQuery()->with($relations);
			$this->items = $query->eagerLoadRelations($this->items);
		}
		return $this;
	}
	public function add($item)
	{
		$this->items[] = $item;
		return $this;
	}
	public function contains($key, $value = null)
	{
		if (func_num_args() == 2) return parent::contains($key, $value);
		if ( ! $this->useAsCallable($key))
		{
			$key = $key instanceof Model ? $key->getKey() : $key;
			return parent::contains(function($k, $m) use ($key)
			{
				return $m->getKey() == $key;
			});
		}
		return parent::contains($key);
	}
	public function fetch($key)
	{
		return new static(array_fetch($this->toArray(), $key));
	}
	public function max($key)
	{
		return $this->reduce(function($result, $item) use ($key)
		{
			return is_null($result) || $item->{$key} > $result ? $item->{$key} : $result;
		});
	}
	public function min($key)
	{
		return $this->reduce(function($result, $item) use ($key)
		{
			return is_null($result) || $item->{$key} < $result ? $item->{$key} : $result;
		});
	}
	public function modelKeys()
	{
		return array_map(function($m) { return $m->getKey(); }, $this->items);
	}
	public function merge($items)
	{
		$dictionary = $this->getDictionary();
		foreach ($items as $item)
		{
			$dictionary[$item->getKey()] = $item;
		}
		return new static(array_values($dictionary));
	}
	public function diff($items)
	{
		$diff = new static;
		$dictionary = $this->getDictionary($items);
		foreach ($this->items as $item)
		{
			if ( ! isset($dictionary[$item->getKey()]))
			{
				$diff->add($item);
			}
		}
		return $diff;
	}
	public function intersect($items)
	{
		$intersect = new static;
		$dictionary = $this->getDictionary($items);
		foreach ($this->items as $item)
		{
			if (isset($dictionary[$item->getKey()]))
			{
				$intersect->add($item);
			}
		}
		return $intersect;
	}
	public function unique()
	{
		$dictionary = $this->getDictionary();
		return new static(array_values($dictionary));
	}
	public function only($keys)
	{
		$dictionary = array_only($this->getDictionary(), $keys);
		return new static(array_values($dictionary));
	}
	public function except($keys)
	{
		$dictionary = array_except($this->getDictionary(), $keys);
		return new static(array_values($dictionary));
	}
	public function getDictionary($items = null)
	{
		$items = is_null($items) ? $this->items : $items;
		$dictionary = array();
		foreach ($items as $value)
		{
			$dictionary[$value->getKey()] = $value;
		}
		return $dictionary;
	}
	public function toBase()
	{
		return new BaseCollection($this->items);
	}
}
