<?php namespace Illuminate\Auth;
use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Console\ClearResetsCommand;
class GeneratorServiceProvider extends ServiceProvider {
	protected $defer = true;
	protected $commands = [
		'ClearResets',
	];
	public function register()
	{
		foreach ($this->commands as $command)
		{
			$this->{"register{$command}Command"}();
		}
		$this->commands(
			'command.auth.resets.clear'
		);
	}
	protected function registerClearResetsCommand()
	{
		$this->app->singleton('command.auth.resets.clear', function()
		{
			return new ClearResetsCommand;
		});
	}
	public function provides()
	{
		return [
			'command.auth.resets.clear',
		];
	}
}
