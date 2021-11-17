<?php namespace Illuminate\Database\Schema\Grammars;
use Illuminate\Support\Fluent;
use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Blueprint;
class MySqlGrammar extends Grammar {
	protected $modifiers = array('Unsigned', 'Nullable', 'Default', 'Increment', 'Comment', 'After');
	protected $serials = array('bigInteger', 'integer', 'mediumInteger', 'smallInteger', 'tinyInteger');
	public function compileTableExists()
	{
		return 'select * from information_schema.tables where table_schema = ? and table_name = ?';
	}
	public function compileColumnExists()
	{
		return "select column_name from information_schema.columns where table_schema = ? and table_name = ?";
	}
	public function compileCreate(Blueprint $blueprint, Fluent $command, Connection $connection)
	{
		$columns = implode(', ', $this->getColumns($blueprint));
		$sql = 'create table '.$this->wrapTable($blueprint)." ($columns)";
		$sql = $this->compileCreateEncoding($sql, $connection);
		if (isset($blueprint->engine))
		{
			$sql .= ' engine = '.$blueprint->engine;
		}
		return $sql;
	}
	protected function compileCreateEncoding($sql, Connection $connection)
	{
		if ( ! is_null($charset = $connection->getConfig('charset')))
		{
			$sql .= ' default character set '.$charset;
		}
		if ( ! is_null($collation = $connection->getConfig('collation')))
		{
			$sql .= ' collate '.$collation;
		}
		return $sql;
	}
	public function compileAdd(Blueprint $blueprint, Fluent $command)
	{
		$table = $this->wrapTable($blueprint);
		$columns = $this->prefixArray('add', $this->getColumns($blueprint));
		return 'alter table '.$table.' '.implode(', ', $columns);
	}
	public function compilePrimary(Blueprint $blueprint, Fluent $command)
	{
		$command->name(null);
		return $this->compileKey($blueprint, $command, 'primary key');
	}
	public function compileUnique(Blueprint $blueprint, Fluent $command)
	{
		return $this->compileKey($blueprint, $command, 'unique');
	}
	public function compileIndex(Blueprint $blueprint, Fluent $command)
	{
		return $this->compileKey($blueprint, $command, 'index');
	}
	protected function compileKey(Blueprint $blueprint, Fluent $command, $type)
	{
		$columns = $this->columnize($command->columns);
		$table = $this->wrapTable($blueprint);
		return "alter table {$table} add {$type} {$command->index}($columns)";
	}
	public function compileDrop(Blueprint $blueprint, Fluent $command)
	{
		return 'drop table '.$this->wrapTable($blueprint);
	}
	public function compileDropIfExists(Blueprint $blueprint, Fluent $command)
	{
		return 'drop table if exists '.$this->wrapTable($blueprint);
	}
	public function compileDropColumn(Blueprint $blueprint, Fluent $command)
	{
		$columns = $this->prefixArray('drop', $this->wrapArray($command->columns));
		$table = $this->wrapTable($blueprint);
		return 'alter table '.$table.' '.implode(', ', $columns);
	}
	public function compileDropPrimary(Blueprint $blueprint, Fluent $command)
	{
		return 'alter table '.$this->wrapTable($blueprint).' drop primary key';
	}
	public function compileDropUnique(Blueprint $blueprint, Fluent $command)
	{
		$table = $this->wrapTable($blueprint);
		return "alter table {$table} drop index {$command->index}";
	}
	public function compileDropIndex(Blueprint $blueprint, Fluent $command)
	{
		$table = $this->wrapTable($blueprint);
		return "alter table {$table} drop index {$command->index}";
	}
	public function compileDropForeign(Blueprint $blueprint, Fluent $command)
	{
		$table = $this->wrapTable($blueprint);
		return "alter table {$table} drop foreign key {$command->index}";
	}
	public function compileRename(Blueprint $blueprint, Fluent $command)
	{
		$from = $this->wrapTable($blueprint);
		return "rename table {$from} to ".$this->wrapTable($command->to);
	}
	protected function typeChar(Fluent $column)
	{
		return "char({$column->length})";
	}
	protected function typeString(Fluent $column)
	{
		return "varchar({$column->length})";
	}
	protected function typeText(Fluent $column)
	{
		return 'text';
	}
	protected function typeMediumText(Fluent $column)
	{
		return 'mediumtext';
	}
	protected function typeLongText(Fluent $column)
	{
		return 'longtext';
	}
	protected function typeBigInteger(Fluent $column)
	{
		return 'bigint';
	}
	protected function typeInteger(Fluent $column)
	{
		return 'int';
	}
	protected function typeMediumInteger(Fluent $column)
	{
		return 'mediumint';
	}
	protected function typeTinyInteger(Fluent $column)
	{
		return 'tinyint';
	}
	protected function typeSmallInteger(Fluent $column)
	{
		return 'smallint';
	}
	protected function typeFloat(Fluent $column)
	{
		return $this->typeDouble($column);
	}
	protected function typeDouble(Fluent $column)
	{
		if ($column->total && $column->places)
		{
			return "double({$column->total}, {$column->places})";
		}
		return 'double';
	}
	protected function typeDecimal(Fluent $column)
	{
		return "decimal({$column->total}, {$column->places})";
	}
	protected function typeBoolean(Fluent $column)
	{
		return 'tinyint(1)';
	}
	protected function typeEnum(Fluent $column)
	{
		return "enum('".implode("', '", $column->allowed)."')";
	}
	protected function typeJson(Fluent $column)
	{
		return 'text';
	}
	protected function typeJsonb(Fluent $column)
	{
		return "text";
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
		if ( ! $column->nullable) return 'timestamp default 0';
		return 'timestamp';
	}
	protected function typeTimestampTz(Fluent $column)
	{
		if ( ! $column->nullable) return 'timestamp default 0';
		return 'timestamp';
	}
	protected function typeBinary(Fluent $column)
	{
		return 'blob';
	}
	protected function modifyUnsigned(Blueprint $blueprint, Fluent $column)
	{
		if ($column->unsigned) return ' unsigned';
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
			return ' auto_increment primary key';
		}
	}
	protected function modifyAfter(Blueprint $blueprint, Fluent $column)
	{
		if ( ! is_null($column->after))
		{
			return ' after '.$this->wrap($column->after);
		}
	}
	protected function modifyComment(Blueprint $blueprint, Fluent $column)
	{
		if ( ! is_null($column->comment))
		{
			return ' comment "'.$column->comment.'"';
		}
	}
	protected function wrapValue($value)
	{
		if ($value === '*') return $value;
		return '`'.str_replace('`', '``', $value).'`';
	}
}
