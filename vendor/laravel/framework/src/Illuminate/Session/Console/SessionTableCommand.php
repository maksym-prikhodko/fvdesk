<?php namespace Illuminate\Session\Console;
use Illuminate\Console\Command;
use Illuminate\Foundation\Composer;
use Illuminate\Filesystem\Filesystem;
class SessionTableCommand extends Command {
	protected $name = 'session:table';
	protected $description = 'Create a migration for the session database table';
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
		$this->files->put($fullPath, $this->files->get(__DIR__.'/stubs/database.stub'));
		$this->info('Migration created successfully!');
		$this->composer->dumpAutoloads();
	}
	protected function createBaseMigration()
	{
		$name = 'create_session_table';
		$path = $this->laravel->databasePath().'/migrations';
		return $this->laravel['migration.creator']->create($name, $path);
	}
}
