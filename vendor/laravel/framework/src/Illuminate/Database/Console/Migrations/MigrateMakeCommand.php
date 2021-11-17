<?php namespace Illuminate\Database\Console\Migrations;
use Illuminate\Foundation\Composer;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Illuminate\Database\Migrations\MigrationCreator;
class MigrateMakeCommand extends BaseCommand {
	protected $name = 'make:migration';
	protected $description = 'Create a new migration file';
	protected $creator;
	protected $composer;
	public function __construct(MigrationCreator $creator, Composer $composer)
	{
		parent::__construct();
		$this->creator = $creator;
		$this->composer = $composer;
	}
	public function fire()
	{
		$name = $this->input->getArgument('name');
		$table = $this->input->getOption('table');
		$create = $this->input->getOption('create');
		if ( ! $table && is_string($create)) $table = $create;
		$this->writeMigration($name, $table, $create);
		$this->composer->dumpAutoloads();
	}
	protected function writeMigration($name, $table, $create)
	{
		$path = $this->getMigrationPath();
		$file = pathinfo($this->creator->create($name, $path, $table, $create), PATHINFO_FILENAME);
		$this->line("<info>Created Migration:</info> $file");
	}
	protected function getArguments()
	{
		return array(
			array('name', InputArgument::REQUIRED, 'The name of the migration'),
		);
	}
	protected function getOptions()
	{
		return array(
			array('create', null, InputOption::VALUE_OPTIONAL, 'The table to be created.'),
			array('table', null, InputOption::VALUE_OPTIONAL, 'The table to migrate.'),
		);
	}
}
