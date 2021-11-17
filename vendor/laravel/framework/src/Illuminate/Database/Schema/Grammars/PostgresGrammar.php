<?php namespace Illuminate\Database\Schema\Grammars;
use Illuminate\Support\Fluent;
use Illuminate\Database\Schema\Blueprint;
class PostgresGrammar extends Grammar {
	protected $modifiers = array('Increment', 'Nullable', 'Default');
	protected $serials = array('bigInteger', 'integer', 'mediumInteger', 'smallInteger', 'tinyInteger');
	public function compileTableExists()
	{
		return 'select * from information_schema.tables where table_name = ?';
	}
	public function compileColumnExists($table)
	{
		return "select column_name from information_schema.columns where table_name = '$table'";
	}
	public function compileCreate(Blueprint $blueprint, Fluent $command)
	{
		$columns = implode(', ', $this->getColumns($blueprint));
		return 'create table '.$this->wrapTable($blueprint)." ($columns)";
	}
	public function compileAdd(Blueprint $blueprint, Fluent $command)
	{
		$table = $this->wrapTable($blueprint);
		$columns = $this->prefixArray('add column', $this->getColumns($blueprint));
		return 'alter table '.$table.' '.implode(', ', $columns);
	}
	public function compilePrimary(Blueprint $blueprint, Fluent $command)
	{
		$columns = $this->columnize($command->columns);
		return 'alter table '.$this->wrapTable($blueprint)." add primary key ({$columns})";
	}
	public function compileUnique(Blueprint $blueprint, Fluent $command)
	{
		$table = $this->wrapTable($blueprint);
		$columns = $this->columnize($command->columns);
		return "alter table $table add constraint {$command->index} unique ($columns)";
	}
	public function compileIndex(Blueprint $blueprint, Fluent $command)
	{
		$columns = $this->columnize($command->columns);
		return "create index {$command->index} on ".$this->wrapTable($blueprint)." ({$columns})";
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
		$columns = $this->prefixArray('drop column', $this->wrapArray($command->columns));
		$table = $this->wrapTable($blueprint);
		return 'alter table '.$table.' '.implode(', ', $columns);
	}
	public function compileDropPrimary(Blueprint $blueprint, Fluent $command)
	{
		$table = $blueprint->getTable();
		return 'alter table '.$this->wrapTable($blueprint)." drop constraint {$table}_pkey";
	}
	public function compileDropUnique(Blueprint $blueprint, Fluent $command)
	{
		$table = $this->wrapTable($blueprint);
		return "alter table {$table} drop constraint {$command->index}";
	}
	public function compileDropIndex(Blueprint $blueprint, Fluent $command)
	{
		return "drop index {$command->index}";
	}
	public function compileDropForeign(Blueprint $blueprint, Fluent $command)
	{
		$table = $this->wrapTable($blueprint);
		return "alter table {$table} drop constraint {$command->index}";
	}
	public function compileRename(Blueprint $blueprint, Fluent $command)
	{
		$from = $this->wrapTable($blueprint);
		return "alter table {$from} rename to ".$this->wrapTable($command->to);
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
		return 'text';
	}
	protected function typeLongText(Fluent $column)
	{
		return 'text';
	}
	protected function typeInteger(Fluent $column)
	{
		return $column->autoIncrement ? 'serial' : 'integer';
	}
	protected function typeBigInteger(Fluent $column)
	{
		return $column->autoIncrement ? 'bigserial' : 'bigint';
	}
	protected function typeMediumInteger(Fluent $column)
	{
		return $column->autoIncrement ? 'serial' : 'integer';
	}
	protected function typeTinyInteger(Fluent $column)
	{
		return $column->autoIncrement ? 'smallserial' : 'smallint';
	}
	protected function typeSmallInteger(Fluent $column)
	{
		return $column->autoIncrement ? 'smallserial' : 'smallint';
	}
	protected function typeFloat(Fluent $column)
	{
		return $this->typeDouble($column);
	}
	protected function typeDouble(Fluent $column)
	{
		return 'double precision';
	}
	protected function typeDecimal(Fluent $column)
	{
		return "decimal({$column->total}, {$column->places})";
	}
	protected function typeBoolean(Fluent $column)
	{
		return 'boolean';
	}
	protected function typeEnum(Fluent $column)
	{
		$allowed = array_map(function($a) { return "'".$a."'"; }, $column->allowed);
		return "varchar(255) check (\"{$column->name}\" in (".implode(', ', $allowed)."))";
	}
	protected function typeJson(Fluent $column)
	{
		return "json";
	}
	protected function typeJsonb(Fluent $column)
	{
		return "jsonb";
	}
	protected function typeDate(Fluent $column)
	{
		return 'date';
	}
	protected function typeDateTime(Fluent $column)
	{
		return 'timestamp(0) without time zone';
	}
	protected function typeDateTimeTz(Fluent $column)
	{
		return 'timestamp(0) with time zone';
	}
	protected function typeTime(Fluent $column)
	{
		return 'time(0) without time zone';
	}
	protected function typeTimeTz(Fluent $column)
	{
		return 'time(0) with time zone';
	}
	protected function typeTimestamp(Fluent $column)
	{
		return 'timestamp(0) without time zone';
	}
	protected function typeTimestampTz(Fluent $column)
	{
		return 'timestamp(0) with time zone';
	}
	protected function typeBinary(Fluent $column)
	{
		return 'bytea';
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
			return ' primary key';
		}
	}
}
