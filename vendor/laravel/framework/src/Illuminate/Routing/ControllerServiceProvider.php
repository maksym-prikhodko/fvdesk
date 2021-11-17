<?php namespace Illuminate\Routing;
use Illuminate\Support\ServiceProvider;
class ControllerServiceProvider extends ServiceProvider {
	public function register()
	{
		$this->app->singleton('illuminate.route.dispatcher', function($app)
		{
			return new ControllerDispatcher($app['router'], $app);
		});
	}
}
