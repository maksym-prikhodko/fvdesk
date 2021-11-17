<?php namespace Illuminate\Support\Facades;
class Bus extends Facade {
	protected static function getFacadeAccessor()
	{
		return 'Illuminate\Contracts\Bus\Dispatcher';
	}
}
