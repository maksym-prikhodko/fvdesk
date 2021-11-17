<?php namespace Illuminate\Bus;
use Illuminate\Support\ServiceProvider;
class BusServiceProvider extends ServiceProvider {
	protected $defer = true;
	public function register()
	{
		$this->app->singleton('Illuminate\Bus\Dispatcher', function($app)
		{
			return new Dispatcher($app, function() use ($app)
			{
				return $app['Illuminate\Contracts\Queue\Queue'];
			});
		});
		$this->app->alias(
			'Illuminate\Bus\Dispatcher', 'Illuminate\Contracts\Bus\Dispatcher'
		);
		$this->app->alias(
			'Illuminate\Bus\Dispatcher', 'Illuminate\Contracts\Bus\QueueingDispatcher'
		);
	}
	public function provides()
	{
		return [
			'Illuminate\Bus\Dispatcher',
			'Illuminate\Contracts\Bus\Dispatcher',
			'Illuminate\Contracts\Bus\QueueingDispatcher',
		];
	}
}
