<?php namespace Illuminate\Queue\Console;
use Illuminate\Console\Command;
use Illuminate\Foundation\Composer;
use Illuminate\Filesystem\Filesystem;
class FailedTableCommand extends Command {
	protected $name = 'queue:failed-table';
	protected $description = 'Create a migration for the failed queue jobs database table';
	protected $files;
	protected $composer;
	public function __construct(Filesystem $files, Composer $composer)
	{
		parent::__construct();
		$this->files = $files;
		$this->composer = $composer;
	}
	public function fire()
	{
		$fullPath = $this->createBaseMigration();
		$this->files->put($fullPath, $this->files->get(__DIR__.'/stubs/failed_jobs.stub'));
		$this->info('Migration created successfully!');
		$this->composer->dumpAutoloads();
	}
	protected function createBaseMigration()
	{
		$name = 'create_failed_jobs_table';
		$path = $this->laravel->databasePath().'/migrations';
		return $this->laravel['migration.creator']->create($name, $path);
	}
}
