<?php namespace Propaganistas\LaravelPhone;
use Illuminate\Support\ServiceProvider;
class LaravelPhoneServiceProvider extends ServiceProvider
{
	protected $defer = false;
	public function boot()
	{
		$this->app['validator']->extend('phone', 'Propaganistas\LaravelPhone\Validator@phone');
	}
	public function register() {}
}
