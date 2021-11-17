<?php namespace Illuminate\Database;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Database\Migrations\MigrationCreator;
use Illuminate\Database\Console\Migrations\ResetCommand;
use Illuminate\Database\Console\Migrations\RefreshCommand;
use Illuminate\Database\Console\Migrations\InstallCommand;
use Illuminate\Database\Console\Migrations\MigrateCommand;
use Illuminate\Database\Console\Migrations\RollbackCommand;
use Illuminate\Database\Console\Migrations\MigrateMakeCommand;
use Illuminate\Database\Console\Migrations\StatusCommand;
use Illuminate\Database\Migrations\DatabaseMigrationRepository;
class MigrationServiceProvider extends ServiceProvider {
	protected $defer = true;
	public function register()
	{
		$this->registerRepository();
		$this->registerMigrator();
		$this->registerCommands();
	}
	protected function registerRepository()
	{
		$this->app->singleton('migration.repository', function($app)
		{
			$table = $app['config']['database.migrations'];
			return new DatabaseMigrationRepository($app['db'], $table);
		});
	}
	protected function registerMigrator()
	{
		$this->app->singleton('migrator', function($app)
		{
			$repository = $app['migration.repository'];
			return new Migrator($repository, $app['db'], $app['files']);
		});
	}
	protected function registerCommands()
	{
		$commands = array('Migrate', 'Rollback', 'Reset', 'Refresh', 'Install', 'Make', 'Status');
		foreach ($commands as $command)
		{
			$this->{'register'.$command.'Command'}();
		}
		$this->commands(
			'command.migrate', 'command.migrate.make',
			'command.migrate.install', 'command.migrate.rollback',
			'command.migrate.reset', 'command.migrate.refresh',
			'command.migrate.status'
		);
	}
	protected function registerMigrateCommand()
	{
		$this->app->singleton('command.migrate', function($app)
		{
			return new MigrateCommand($app['migrator']);
		});
	}
	protected function registerRollbackCommand()
	{
		$this->app->singleton('command.migrate.rollback', function($app)
		{
			return new RollbackCommand($app['migrator']);
		});
	}
	protected function registerResetCommand()
	{
		$this->app->singleton('command.migrate.reset', function($app)
		{
			return new ResetCommand($app['migrator']);
		});
	}
	protected function registerRefreshCommand()
	{
		$this->app->singleton('command.migrate.refresh', function()
		{
			return new RefreshCommand;
		});
	}
	protected function registerStatusCommand()
	{
		$this->app->singleton('command.migrate.status', function($app)
		{
			return new StatusCommand($app['migrator']);
		});
	}
	protected function registerInstallCommand()
	{
		$this->app->singleton('command.migrate.install', function($app)
		{
			return new InstallCommand($app['migration.repository']);
		});
	}
	protected function registerMakeCommand()
	{
		$this->registerCreator();
		$this->app->singleton('command.migrate.make', function($app)
		{
			$creator = $app['migration.creator'];
			$composer = $app['composer'];
			return new MigrateMakeCommand($creator, $composer);
		});
	}
	protected function registerCreator()
	{
		$this->app->singleton('migration.creator', function($app)
		{
			return new MigrationCreator($app['files']);
		});
	}
	public function provides()
	{
		return array(
			'migrator', 'migration.repository', 'command.migrate',
			'command.migrate.rollback', 'command.migrate.reset',
			'command.migrate.refresh', 'command.migrate.install',
			'command.migrate.status', 'migration.creator',
			'command.migrate.make',
		);
	}
}
