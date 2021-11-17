<?php namespace Illuminate\Foundation\Providers;
use Illuminate\Support\AggregateServiceProvider;
class ConsoleSupportServiceProvider extends AggregateServiceProvider {
	protected $defer = true;
	protected $providers = [
		'Illuminate\Auth\GeneratorServiceProvider',
		'Illuminate\Console\ScheduleServiceProvider',
		'Illuminate\Database\MigrationServiceProvider',
		'Illuminate\Database\SeedServiceProvider',
		'Illuminate\Foundation\Providers\ComposerServiceProvider',
		'Illuminate\Queue\ConsoleServiceProvider',
		'Illuminate\Routing\GeneratorServiceProvider',
		'Illuminate\Session\CommandsServiceProvider',
	];
}
