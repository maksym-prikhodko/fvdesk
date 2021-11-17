<?php namespace Illuminate\Cache;
abstract class TaggableStore {
	public function section($name)
	{
		return $this->tags($name);
	}
	public function tags($names)
	{
		return new TaggedCache($this, new TagSet($this, is_array($names) ? $names : func_get_args()));
	}
}
