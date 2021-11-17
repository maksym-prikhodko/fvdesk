<?php namespace Illuminate\Console;
use Illuminate\Support\ServiceProvider;
class ScheduleServiceProvider extends ServiceProvider {
	protected $defer = true;
	public function register()
	{
		$this->commands('Illuminate\Console\Scheduling\ScheduleRunCommand');
	}
	public function provides()
	{
		return [
			'Illuminate\Console\Scheduling\ScheduleRunCommand',
		];
	}
}
