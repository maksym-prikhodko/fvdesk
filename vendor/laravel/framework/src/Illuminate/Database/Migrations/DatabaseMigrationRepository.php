<?php namespace Illuminate\Database\Migrations;
use Illuminate\Database\ConnectionResolverInterface as Resolver;
class DatabaseMigrationRepository implements MigrationRepositoryInterface {
	protected $resolver;
	protected $table;
	protected $connection;
	public function __construct(Resolver $resolver, $table)
	{
		$this->table = $table;
		$this->resolver = $resolver;
	}
	public function getRan()
	{
		return $this->table()->lists('migration');
	}
	public function getLast()
	{
		$query = $this->table()->where('batch', $this->getLastBatchNumber());
		return $query->orderBy('migration', 'desc')->get();
	}
	public function log($file, $batch)
	{
		$record = array('migration' => $file, 'batch' => $batch);
		$this->table()->insert($record);
	}
	public function delete($migration)
	{
		$this->table()->where('migration', $migration->migration)->delete();
	}
	public function getNextBatchNumber()
	{
		return $this->getLastBatchNumber() + 1;
	}
	public function getLastBatchNumber()
	{
		return $this->table()->max('batch');
	}
	public function createRepository()
	{
		$schema = $this->getConnection()->getSchemaBuilder();
		$schema->create($this->table, function($table)
		{
			$table->string('migration');
			$table->integer('batch');
		});
	}
	public function repositoryExists()
	{
		$schema = $this->getConnection()->getSchemaBuilder();
		return $schema->hasTable($this->table);
	}
	protected function table()
	{
		return $this->getConnection()->table($this->table);
	}
	public function getConnectionResolver()
	{
		return $this->resolver;
	}
	public function getConnection()
	{
		return $this->resolver->connection($this->connection);
	}
	public function setSource($name)
	{
		$this->connection = $name;
	}
}
