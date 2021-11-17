<?php namespace Illuminate\Database\Schema;
use Closure;
use Illuminate\Database\Connection;
class Builder {
	protected $connection;
	protected $grammar;
	protected $resolver;
	public function __construct(Connection $connection)
	{
		$this->connection = $connection;
		$this->grammar = $connection->getSchemaGrammar();
	}
	public function hasTable($table)
	{
		$sql = $this->grammar->compileTableExists();
		$table = $this->connection->getTablePrefix().$table;
		return count($this->connection->select($sql, array($table))) > 0;
	}
	public function hasColumn($table, $column)
	{
		$column = strtolower($column);
		return in_array($column, array_map('strtolower', $this->getColumnListing($table)));
	}
	public function hasColumns($table, array $columns)
	{
		$tableColumns = array_map('strtolower', $this->getColumnListing($table));
		foreach ($columns as $column)
		{
			if ( ! in_array(strtolower($column), $tableColumns)) return false;
		}
		return true;
	}
	public function getColumnListing($table)
	{
		$table = $this->connection->getTablePrefix().$table;
		$results = $this->connection->select($this->grammar->compileColumnExists($table));
		return $this->connection->getPostProcessor()->processColumnListing($results);
	}
	public function table($table, Closure $callback)
	{
		$this->build($this->createBlueprint($table, $callback));
	}
	public function create($table, Closure $callback)
	{
		$blueprint = $this->createBlueprint($table);
		$blueprint->create();
		$callback($blueprint);
		$this->build($blueprint);
	}
	public function drop($table)
	{
		$blueprint = $this->createBlueprint($table);
		$blueprint->drop();
		$this->build($blueprint);
	}
	public function dropIfExists($table)
	{
		$blueprint = $this->createBlueprint($table);
		$blueprint->dropIfExists();
		$this->build($blueprint);
	}
	public function rename($from, $to)
	{
		$blueprint = $this->createBlueprint($from);
		$blueprint->rename($to);
		$this->build($blueprint);
	}
	protected function build(Blueprint $blueprint)
	{
		$blueprint->build($this->connection, $this->grammar);
	}
	protected function createBlueprint($table, Closure $callback = null)
	{
		if (isset($this->resolver))
		{
			return call_user_func($this->resolver, $table, $callback);
		}
		return new Blueprint($table, $callback);
	}
	public function getConnection()
	{
		return $this->connection;
	}
	public function setConnection(Connection $connection)
	{
		$this->connection = $connection;
		return $this;
	}
	public function blueprintResolver(Closure $resolver)
	{
		$this->resolver = $resolver;
	}
}
