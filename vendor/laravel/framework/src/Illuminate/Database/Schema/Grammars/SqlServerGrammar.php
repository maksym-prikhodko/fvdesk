<?php namespace Illuminate\Database\Schema\Grammars;
use Illuminate\Support\Fluent;
use Illuminate\Database\Schema\Blueprint;
class SqlServerGrammar extends Grammar {
	protected $modifiers = array('Increment', 'Nullable', 'Default');
	protected $serials = array('bigInteger', 'integer');
	public function compileTableExists()
	{
		return "select * from sysobjects where type = 'U' and name = ?";
	}
	public function compileColumnExists($table)
	{
		return "select col.name from sys.columns as col
                join sys.objects as obj on col.object_id = obj.object_id
                where obj.type = 'U' and obj.name = '$table'";
	}
	public function compileCreate(Blueprint $blueprint, Fluent $command)
	{
		$columns = implode(', ', $this->getColumns($blueprint));
		return 'create table '.$this->wrapTable($blueprint)." ($columns)";
	}
	public function compileAdd(Blueprint $blueprint, Fluent $command)
	{
		$table = $this->wrapTable($blueprint);
		$columns = $this->getColumns($blueprint);
		return 'alter table '.$table.' add '.implode(', ', $columns);
	}
	public function compilePrimary(Blueprint $blueprint, Fluent $command)
	{
		$columns = $this->columnize($command->columns);
		$table = $this->wrapTable($blueprint);
		return "alter table {$table} add constraint {$command->index} primary key ({$columns})";
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
	public function compileDrop(Blueprint $blueprint, Fluent $command)
	{
		return 'drop table '.$this->wrapTable($blueprint);
	}
	public function compileDropIfExists(Blueprint $blueprint, Fluent $command)
	{
		return 'if exists (select * from INFORMATION_SCHEMA.TABLES where TABLE_NAME = \''.$blueprint->getTable().'\') drop table '.$blueprint->getTable();
	}
	public function compileDropColumn(Blueprint $blueprint, Fluent $command)
	{
		$columns = $this->wrapArray($command->columns);
		$table = $this->wrapTable($blueprint);
		return 'alter table '.$table.' drop column '.implode(', ', $columns);
	}
	public function compileDropPrimary(Blueprint $blueprint, Fluent $command)
	{
		$table = $this->wrapTable($blueprint);
		return "alter table {$table} drop constraint {$command->index}";
	}
	public function compileDropUnique(Blueprint $blueprint, Fluent $command)
	{
		$table = $this->wrapTable($blueprint);
		return "drop index {$command->index} on {$table}";
	}
	public function compileDropIndex(Blueprint $blueprint, Fluent $command)
	{
		$table = $this->wrapTable($blueprint);
		return "drop index {$command->index} on {$table}";
	}
	public function compileDropForeign(Blueprint $blueprint, Fluent $command)
	{
		$table = $this->wrapTable($blueprint);
		return "alter table {$table} drop constraint {$command->index}";
	}
	public function compileRename(Blueprint $blueprint, Fluent $command)
	{
		$from = $this->wrapTable($blueprint);
		return "sp_rename {$from}, ".$this->wrapTable($command->to);
	}
	protected function typeChar(Fluent $column)
	{
		return "nchar({$column->length})";
	}
	protected function typeString(Fluent $column)
	{
		return "nvarchar({$column->length})";
	}
	protected function typeText(Fluent $column)
	{
		return 'nvarchar(max)';
	}
	protected function typeMediumText(Fluent $column)
	{
		return 'nvarchar(max)';
	}
	protected function typeLongText(Fluent $column)
	{
		return 'nvarchar(max)';
	}
	protected function typeInteger(Fluent $column)
	{
		return 'int';
	}
	protected function typeBigInteger(Fluent $column)
	{
		return 'bigint';
	}
	protected function typeMediumInteger(Fluent $column)
	{
		return 'int';
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
		return 'float';
	}
	protected function typeDouble(Fluent $column)
	{
		return 'float';
	}
	protected function typeDecimal(Fluent $column)
	{
		return "decimal({$column->total}, {$column->places})";
	}
	protected function typeBoolean(Fluent $column)
	{
		return 'bit';
	}
	protected function typeEnum(Fluent $column)
	{
		return 'nvarchar(255)';
	}
	protected function typeJson(Fluent $column)
	{
		return 'nvarchar(max)';
	}
	protected function typeJsonb(Fluent $column)
	{
		return 'nvarchar(max)';
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
		return 'datetimeoffset(0)';
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
		return 'datetimeoffset(0)';
	}
	protected function typeBinary(Fluent $column)
	{
		return 'varbinary(max)';
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
			return ' identity primary key';
		}
	}
}
