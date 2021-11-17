<?php namespace Illuminate\Support\Facades;
class Artisan extends Facade {
	protected static function getFacadeAccessor()
	{
		return 'Illuminate\Contracts\Console\Kernel';
	}
}
