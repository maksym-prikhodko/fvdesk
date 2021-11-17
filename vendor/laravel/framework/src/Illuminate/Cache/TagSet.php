<?php namespace Illuminate\Cache;
use Illuminate\Contracts\Cache\Store;
class TagSet {
	protected $store;
	protected $names = array();
	public function __construct(Store $store, array $names = array())
	{
		$this->store = $store;
		$this->names = $names;
	}
	public function reset()
	{
		array_walk($this->names, array($this, 'resetTag'));
	}
	public function tagId($name)
	{
		return $this->store->get($this->tagKey($name)) ?: $this->resetTag($name);
	}
	protected function tagIds()
	{
		return array_map(array($this, 'tagId'), $this->names);
	}
	public function getNamespace()
	{
		return implode('|', $this->tagIds());
	}
	public function resetTag($name)
	{
		$this->store->forever($this->tagKey($name), $id = str_replace('.', '', uniqid('', true)));
		return $id;
	}
	public function tagKey($name)
	{
		return 'tag:'.$name.':key';
	}
}
