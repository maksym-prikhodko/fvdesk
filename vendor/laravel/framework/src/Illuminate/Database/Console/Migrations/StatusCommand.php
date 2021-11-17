<?php namespace Illuminate\Database\Console\Migrations;
use Illuminate\Database\Migrations\Migrator;
class StatusCommand extends BaseCommand {
	protected $name = 'migrate:status';
	protected $description = 'Show the status of each migration';
	protected $migrator;
	public function __construct(Migrator $migrator)
	{
		parent::__construct();
		$this->migrator = $migrator;
	}
	public function fire()
	{
		if ( ! $this->migrator->repositoryExists())
		{
			return $this->error('No migrations found.');
		}
		$ran = $this->migrator->getRepository()->getRan();
		$migrations = [];
		foreach ($this->getAllMigrationFiles() as $migration)
		{
			$migrations[] = in_array($migration, $ran) ? ['<info>Y</info>', $migration] : ['<fg=red>N</fg=red>', $migration];
		}
		if (count($migrations) > 0)
		{
			$this->table(['Ran?', 'Migration'], $migrations);
		}
		else
		{
			$this->error('No migrations found');
		}
	}
	protected function getAllMigrationFiles()
	{
		return $this->migrator->getMigrationFiles($this->getMigrationPath());
	}
}
