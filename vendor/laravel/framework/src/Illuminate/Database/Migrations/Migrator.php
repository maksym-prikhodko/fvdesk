<?php namespace Illuminate\Database\Migrations;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Database\ConnectionResolverInterface as Resolver;
class Migrator {
	protected $repository;
	protected $files;
	protected $resolver;
	protected $connection;
	protected $notes = array();
	public function __construct(MigrationRepositoryInterface $repository,
								Resolver $resolver,
                                Filesystem $files)
	{
		$this->files = $files;
		$this->resolver = $resolver;
		$this->repository = $repository;
	}
	public function run($path, $pretend = false)
	{
		$this->notes = array();
		$files = $this->getMigrationFiles($path);
		$ran = $this->repository->getRan();
		$migrations = array_diff($files, $ran);
		$this->requireFiles($path, $migrations);
		$this->runMigrationList($migrations, $pretend);
	}
	public function runMigrationList($migrations, $pretend = false)
	{
		if (count($migrations) == 0)
		{
			$this->note('<info>Nothing to migrate.</info>');
			return;
		}
		$batch = $this->repository->getNextBatchNumber();
		foreach ($migrations as $file)
		{
			$this->runUp($file, $batch, $pretend);
		}
	}
	protected function runUp($file, $batch, $pretend)
	{
		$migration = $this->resolve($file);
		if ($pretend)
		{
			return $this->pretendToRun($migration, 'up');
		}
		$migration->up();
		$this->repository->log($file, $batch);
		$this->note("<info>Migrated:</info> $file");
	}
	public function rollback($pretend = false)
	{
		$this->notes = array();
		$migrations = $this->repository->getLast();
		if (count($migrations) == 0)
		{
			$this->note('<info>Nothing to rollback.</info>');
			return count($migrations);
		}
		foreach ($migrations as $migration)
		{
			$this->runDown((object) $migration, $pretend);
		}
		return count($migrations);
	}
	public function reset($pretend = false)
	{
		$this->notes = [];
		$migrations = array_reverse($this->repository->getRan());
		if (count($migrations) == 0)
		{
			$this->note('<info>Nothing to rollback.</info>');
			return count($migrations);
		}
		foreach ($migrations as $migration)
		{
			$this->runDown((object) ['migration' => $migration], $pretend);
		}
		return count($migrations);
	}
	protected function runDown($migration, $pretend)
	{
		$file = $migration->migration;
		$instance = $this->resolve($file);
		if ($pretend)
		{
			return $this->pretendToRun($instance, 'down');
		}
		$instance->down();
		$this->repository->delete($migration);
		$this->note("<info>Rolled back:</info> $file");
	}
	public function getMigrationFiles($path)
	{
		$files = $this->files->glob($path.'
	public function requireFiles($path, array $files)
	{
		foreach ($files as $file) $this->files->requireOnce($path.'/'.$file.'.php');
	}
	protected function pretendToRun($migration, $method)
	{
		foreach ($this->getQueries($migration, $method) as $query)
		{
			$name = get_class($migration);
			$this->note("<info>{$name}:</info> {$query['query']}");
		}
	}
	protected function getQueries($migration, $method)
	{
		$connection = $migration->getConnection();
		$db = $this->resolveConnection($connection);
		return $db->pretend(function() use ($migration, $method)
		{
			$migration->$method();
		});
	}
	public function resolve($file)
	{
		$file = implode('_', array_slice(explode('_', $file), 4));
		$class = studly_case($file);
		return new $class;
	}
	protected function note($message)
	{
		$this->notes[] = $message;
	}
	public function getNotes()
	{
		return $this->notes;
	}
	public function resolveConnection($connection)
	{
		return $this->resolver->connection($connection);
	}
	public function setConnection($name)
	{
		if ( ! is_null($name))
		{
			$this->resolver->setDefaultConnection($name);
		}
		$this->repository->setSource($name);
		$this->connection = $name;
	}
	public function getRepository()
	{
		return $this->repository;
	}
	public function repositoryExists()
	{
		return $this->repository->repositoryExists();
	}
	public function getFilesystem()
	{
		return $this->files;
	}
}
