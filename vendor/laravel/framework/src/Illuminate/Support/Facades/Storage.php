<?php namespace Illuminate\Support\Facades;
class Storage extends Facade {
	protected static function getFacadeAccessor()
	{
		return 'filesystem';
	}
}
