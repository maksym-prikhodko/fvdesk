<?php namespace Illuminate\Database\Console\Migrations;
use Illuminate\Console\ConfirmableTrait;
use Illuminate\Database\Migrations\Migrator;
use Symfony\Component\Console\Input\InputOption;
class MigrateCommand extends BaseCommand {
	use ConfirmableTrait;
	protected $name = 'migrate';
	protected $description = 'Run the database migrations';
	protected $migrator;
	public function __construct(Migrator $migrator)
	{
		parent::__construct();
		$this->migrator = $migrator;
	}
	public function fire()
	{
		if ( ! $this->confirmToProceed()) return;
		$this->prepareDatabase();
		$pretend = $this->input->getOption('pretend');
		if ( ! is_null($path = $this->input->getOption('path')))
		{
			$path = $this->laravel->basePath().'/'.$path;
		}
		else
		{
			$path = $this->getMigrationPath();
		}
		$this->migrator->run($path, $pretend);
		foreach ($this->migrator->getNotes() as $note)
		{
			$this->output->writeln($note);
		}
		if ($this->input->getOption('seed'))
		{
			$this->call('db:seed', ['--force' => true]);
		}
	}
	protected function prepareDatabase()
	{
		$this->migrator->setConnection($this->input->getOption('database'));
		if ( ! $this->migrator->repositoryExists())
		{
			$options = array('--database' => $this->input->getOption('database'));
			$this->call('migrate:install', $options);
		}
	}
	protected function getOptions()
	{
		return array(
			array('database', null, InputOption::VALUE_OPTIONAL, 'The database connection to use.'),
			array('force', null, InputOption::VALUE_NONE, 'Force the operation to run when in production.'),
			array('path', null, InputOption::VALUE_OPTIONAL, 'The path of migrations files to be executed.'),
			array('pretend', null, InputOption::VALUE_NONE, 'Dump the SQL queries that would be run.'),
			array('seed', null, InputOption::VALUE_NONE, 'Indicates if the seed task should be re-run.'),
		);
	}
}
