<?php namespace Illuminate\Database;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Console\SeedCommand;
class SeedServiceProvider extends ServiceProvider {
	protected $defer = true;
	public function register()
	{
		$this->registerSeedCommand();
		$this->app->singleton('seeder', function()
		{
			return new Seeder;
		});
		$this->commands('command.seed');
	}
	protected function registerSeedCommand()
	{
		$this->app->singleton('command.seed', function($app)
		{
			return new SeedCommand($app['db']);
		});
	}
	public function provides()
	{
		return array('seeder', 'command.seed');
	}
}
