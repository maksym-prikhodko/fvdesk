<?php namespace Illuminate\Cache;
class RedisTaggedCache extends TaggedCache {
	public function forever($key, $value)
	{
		$this->pushForeverKeys($namespace = $this->tags->getNamespace(), $key);
		$this->store->forever(sha1($namespace).':'.$key, $value);
	}
	public function flush()
	{
		$this->deleteForeverKeys();
		parent::flush();
	}
	protected function pushForeverKeys($namespace, $key)
	{
		$fullKey = $this->getPrefix().sha1($namespace).':'.$key;
		foreach (explode('|', $namespace) as $segment)
		{
			$this->store->connection()->lpush($this->foreverKey($segment), $fullKey);
		}
	}
	protected function deleteForeverKeys()
	{
		foreach (explode('|', $this->tags->getNamespace()) as $segment)
		{
			$this->deleteForeverValues($segment = $this->foreverKey($segment));
			$this->store->connection()->del($segment);
		}
	}
	protected function deleteForeverValues($foreverKey)
	{
		$forever = array_unique($this->store->connection()->lrange($foreverKey, 0, -1));
		if (count($forever) > 0)
		{
			call_user_func_array(array($this->store->connection(), 'del'), $forever);
		}
	}
	protected function foreverKey($segment)
	{
		return $this->getPrefix().$segment.':forever';
	}
}
