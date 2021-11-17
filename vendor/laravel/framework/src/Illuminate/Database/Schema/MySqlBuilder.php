<?php namespace Illuminate\Database\Schema;
class MySqlBuilder extends Builder {
	public function hasTable($table)
	{
		$sql = $this->grammar->compileTableExists();
		$database = $this->connection->getDatabaseName();
		$table = $this->connection->getTablePrefix().$table;
		return count($this->connection->select($sql, array($database, $table))) > 0;
	}
	public function getColumnListing($table)
	{
		$sql = $this->grammar->compileColumnExists();
		$database = $this->connection->getDatabaseName();
		$table = $this->connection->getTablePrefix().$table;
		$results = $this->connection->select($sql, array($database, $table));
		return $this->connection->getPostProcessor()->processColumnListing($results);
	}
}
