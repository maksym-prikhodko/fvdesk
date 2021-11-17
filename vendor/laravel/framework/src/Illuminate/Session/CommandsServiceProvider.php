<?php namespace Illuminate\Session;
use Illuminate\Support\ServiceProvider;
class CommandsServiceProvider extends ServiceProvider {
	protected $defer = true;
	public function register()
	{
		$this->app->singleton('command.session.database', function($app)
		{
			return new Console\SessionTableCommand($app['files'], $app['composer']);
		});
		$this->commands('command.session.database');
	}
	public function provides()
	{
		return array('command.session.database');
	}
}
