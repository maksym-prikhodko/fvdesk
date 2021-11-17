<?php namespace Illuminate\Support\Facades;
class Response extends Facade {
	protected static function getFacadeAccessor()
	{
		return 'Illuminate\Contracts\Routing\ResponseFactory';
	}
}
