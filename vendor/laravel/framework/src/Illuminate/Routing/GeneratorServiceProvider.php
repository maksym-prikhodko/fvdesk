<?php namespace Illuminate\Routing;
use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Console\MiddlewareMakeCommand;
use Illuminate\Routing\Console\ControllerMakeCommand;
class GeneratorServiceProvider extends ServiceProvider {
	protected $defer = true;
	public function register()
	{
		$this->registerControllerGenerator();
		$this->registerMiddlewareGenerator();
		$this->commands('command.controller.make', 'command.middleware.make');
	}
	protected function registerControllerGenerator()
	{
		$this->app->singleton('command.controller.make', function($app)
		{
			return new ControllerMakeCommand($app['files']);
		});
	}
	protected function registerMiddlewareGenerator()
	{
		$this->app->singleton('command.middleware.make', function($app)
		{
			return new MiddlewareMakeCommand($app['files']);
		});
	}
	public function provides()
	{
		return array(
			'command.controller.make', 'command.middleware.make',
		);
	}
}
