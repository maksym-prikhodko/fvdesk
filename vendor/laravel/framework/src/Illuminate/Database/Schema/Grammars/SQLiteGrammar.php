<?php namespace Illuminate\Database\Schema\Grammars;
use Illuminate\Support\Fluent;
use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Blueprint;
class SQLiteGrammar extends Grammar {
	protected $modifiers = array('Nullable', 'Default', 'Increment');
	protected $serials = array('bigInteger', 'integer');
	public function compileTableExists()
	{
		return "select * from sqlite_master where type = 'table' and name = ?";
	}
	public function compileColumnExists($table)
	{
		return 'pragma table_info('.str_replace('.', '__', $table).')';
	}
	public function compileCreate(Blueprint $blueprint, Fluent $command)
	{
		$columns = implode(', ', $this->getColumns($blueprint));
		$sql = 'create table '.$this->wrapTable($blueprint)." ($columns";
		$sql .= (string) $this->addForeignKeys($blueprint);
		$sql .= (string) $this->addPrimaryKeys($blueprint);
		return $sql.')';
	}
	protected function addForeignKeys(Blueprint $blueprint)
	{
		$sql = '';
		$foreigns = $this->getCommandsByName($blueprint, 'foreign');
		foreach ($foreigns as $foreign)
		{
			$sql .= $this->getForeignKey($foreign);
			if ( ! is_null($foreign->onDelete))
			{
				$sql .= " on delete {$foreign->onDelete}";
			}
			if ( ! is_null($foreign->onUpdate))
			{
				$sql .= " on update {$foreign->onUpdate}";
			}
		}
		return $sql;
	}
	protected function getForeignKey($foreign)
	{
		$on = $this->wrapTable($foreign->on);
		$columns = $this->columnize($foreign->columns);
		$onColumns = $this->columnize((array) $foreign->references);
		return ", foreign key($columns) references $on($onColumns)";
	}
	protected function addPrimaryKeys(Blueprint $blueprint)
	{
		$primary = $this->getCommandByName($blueprint, 'primary');
		if ( ! is_null($primary))
		{
			$columns = $this->columnize($primary->columns);
			return ", primary key ({$columns})";
		}
	}
	public function compileAdd(Blueprint $blueprint, Fluent $command)
	{
		$table = $this->wrapTable($blueprint);
		$columns = $this->prefixArray('add column', $this->getColumns($blueprint));
		$statements = array();
		foreach ($columns as $column)
		{
			$statements[] = 'alter table '.$table.' '.$column;
		}
		return $statements;
	}
	public function compileUnique(Blueprint $blueprint, Fluent $command)
	{
		$columns = $this->columnize($command->columns);
		$table = $this->wrapTable($blueprint);
		return "create unique index {$command->index} on {$table} ({$columns})";
	}
	public function compileIndex(Blueprint $blueprint, Fluent $command)
	{
		$columns = $this->columnize($command->columns);
		$table = $this->wrapTable($blueprint);
		return "create index {$command->index} on {$table} ({$columns})";
	}
	public function compileForeign(Blueprint $blueprint, Fluent $command)
	{
	}
	public function compileDrop(Blueprint $blueprint, Fluent $command)
	{
		return 'drop table '.$this->wrapTable($blueprint);
	}
	public function compileDropIfExists(Blueprint $blueprint, Fluent $command)
	{
		return 'drop table if exists '.$this->wrapTable($blueprint);
	}
	public function compileDropColumn(Blueprint $blueprint, Fluent $command, Connection $connection)
	{
		$schema = $connection->getDoctrineSchemaManager();
		$tableDiff = $this->getDoctrineTableDiff($blueprint, $schema);
		foreach ($command->columns as $name)
		{
			$column = $connection->getDoctrineColumn($blueprint->getTable(), $name);
			$tableDiff->removedColumns[$name] = $column;
		}
		return (array) $schema->getDatabasePlatform()->getAlterTableSQL($tableDiff);
	}
	public function compileDropUnique(Blueprint $blueprint, Fluent $command)
	{
		return "drop index {$command->index}";
	}
	public function compileDropIndex(Blueprint $blueprint, Fluent $command)
	{
		return "drop index {$command->index}";
	}
	public function compileRename(Blueprint $blueprint, Fluent $command)
	{
		$from = $this->wrapTable($blueprint);
		return "alter table {$from} rename to ".$this->wrapTable($command->to);
	}
	protected function typeChar(Fluent $column)
	{
		return 'varchar';
	}
	protected function typeString(Fluent $column)
	{
		return 'varchar';
	}
	protected function typeText(Fluent $column)
	{
		return 'text';
	}
	protected function typeMediumText(Fluent $column)
	{
		return 'text';
	}
	protected function typeLongText(Fluent $column)
	{
		return 'text';
	}
	protected function typeInteger(Fluent $column)
	{
		return 'integer';
	}
	protected function typeBigInteger(Fluent $column)
	{
		return 'integer';
	}
	protected function typeMediumInteger(Fluent $column)
	{
		return 'integer';
	}
	protected function typeTinyInteger(Fluent $column)
	{
		return 'integer';
	}
	protected function typeSmallInteger(Fluent $column)
	{
		return 'integer';
	}
	protected function typeFloat(Fluent $column)
	{
		return 'float';
	}
	protected function typeDouble(Fluent $column)
	{
		return 'float';
	}
	protected function typeDecimal(Fluent $column)
	{
		return 'numeric';
	}
	protected function typeBoolean(Fluent $column)
	{
		return 'tinyint';
	}
	protected function typeEnum(Fluent $column)
	{
		return 'varchar';
	}
	protected function typeJson(Fluent $column)
	{
		return 'text';
	}
	protected function typeJsonb(Fluent $column)
	{
		return 'text';
	}
	protected function typeDate(Fluent $column)
	{
		return 'date';
	}
	protected function typeDateTime(Fluent $column)
	{
		return 'datetime';
	}
	protected function typeDateTimeTz(Fluent $column)
	{
		return 'datetime';
	}
	protected function typeTime(Fluent $column)
	{
		return 'time';
	}
	protected function typeTimeTz(Fluent $column)
	{
		return 'time';
	}
	protected function typeTimestamp(Fluent $column)
	{
		return 'datetime';
	}
	protected function typeTimestampTz(Fluent $column)
	{
		return 'datetime';
	}
	protected function typeBinary(Fluent $column)
	{
		return 'blob';
	}
	protected function modifyNullable(Blueprint $blueprint, Fluent $column)
	{
		return $column->nullable ? ' null' : ' not null';
	}
	protected function modifyDefault(Blueprint $blueprint, Fluent $column)
	{
		if ( ! is_null($column->default))
		{
			return " default ".$this->getDefaultValue($column->default);
		}
	}
	protected function modifyIncrement(Blueprint $blueprint, Fluent $column)
	{
		if (in_array($column->type, $this->serials) && $column->autoIncrement)
		{
			return ' primary key autoincrement';
		}
	}
}
