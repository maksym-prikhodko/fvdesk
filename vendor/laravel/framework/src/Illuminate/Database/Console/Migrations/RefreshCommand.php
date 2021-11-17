<?php namespace Illuminate\Database\Console\Migrations;
use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Symfony\Component\Console\Input\InputOption;
class RefreshCommand extends Command {
	use ConfirmableTrait;
	protected $name = 'migrate:refresh';
	protected $description = 'Reset and re-run all migrations';
	public function fire()
	{
		if ( ! $this->confirmToProceed()) return;
		$database = $this->input->getOption('database');
		$force = $this->input->getOption('force');
		$this->call('migrate:reset', array(
			'--database' => $database, '--force' => $force,
		));
		$this->call('migrate', array(
			'--database' => $database, '--force' => $force,
		));
		if ($this->needsSeeding())
		{
			$this->runSeeder($database);
		}
	}
	protected function needsSeeding()
	{
		return $this->option('seed') || $this->option('seeder');
	}
	protected function runSeeder($database)
	{
		$class = $this->option('seeder') ?: 'DatabaseSeeder';
		$this->call('db:seed', array('--database' => $database, '--class' => $class));
	}
	protected function getOptions()
	{
		return array(
			array('database', null, InputOption::VALUE_OPTIONAL, 'The database connection to use.'),
			array('force', null, InputOption::VALUE_NONE, 'Force the operation to run when in production.'),
			array('seed', null, InputOption::VALUE_NONE, 'Indicates if the seed task should be re-run.'),
			array('seeder', null, InputOption::VALUE_OPTIONAL, 'The class name of the root seeder.'),
		);
	}
}
