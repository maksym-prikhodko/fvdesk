<?php namespace Illuminate\Support;
use Countable;
use Illuminate\Contracts\Support\MessageBag as MessageBagContract;
class ViewErrorBag implements Countable {
	protected $bags = [];
	public function hasBag($key = 'default')
	{
		return isset($this->bags[$key]);
	}
	public function getBag($key)
	{
		return array_get($this->bags, $key, new MessageBag);
	}
	public function getBags()
	{
		return $this->bags;
	}
	public function put($key, MessageBagContract $bag)
	{
		$this->bags[$key] = $bag;
		return $this;
	}
	public function count()
	{
		return $this->default->count();
	}
	public function __call($method, $parameters)
	{
		return call_user_func_array(array($this->default, $method), $parameters);
	}
	public function __get($key)
	{
		return array_get($this->bags, $key, new MessageBag);
	}
	public function __set($key, $value)
	{
		array_set($this->bags, $key, $value);
	}
}
