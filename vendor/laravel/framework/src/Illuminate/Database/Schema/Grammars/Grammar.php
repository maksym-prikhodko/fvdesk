<?php namespace Illuminate\Database\Schema\Grammars;
use Doctrine\DBAL\Types\Type;
use Illuminate\Support\Fluent;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\TableDiff;
use Illuminate\Database\Connection;
use Doctrine\DBAL\Schema\Comparator;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Grammar as BaseGrammar;
use Doctrine\DBAL\Schema\AbstractSchemaManager as SchemaManager;
abstract class Grammar extends BaseGrammar {
	public function compileRenameColumn(Blueprint $blueprint, Fluent $command, Connection $connection)
	{
		$schema = $connection->getDoctrineSchemaManager();
		$table = $this->getTablePrefix().$blueprint->getTable();
		$column = $connection->getDoctrineColumn($table, $command->from);
		$tableDiff = $this->getRenamedDiff($blueprint, $command, $column, $schema);
		return (array) $schema->getDatabasePlatform()->getAlterTableSQL($tableDiff);
	}
	protected function getRenamedDiff(Blueprint $blueprint, Fluent $command, Column $column, SchemaManager $schema)
	{
		$tableDiff = $this->getDoctrineTableDiff($blueprint, $schema);
		return $this->setRenamedColumns($tableDiff, $command, $column);
	}
	protected function setRenamedColumns(TableDiff $tableDiff, Fluent $command, Column $column)
	{
		$newColumn = new Column($command->to, $column->getType(), $column->toArray());
		$tableDiff->renamedColumns = array($command->from => $newColumn);
		return $tableDiff;
	}
	public function compileForeign(Blueprint $blueprint, Fluent $command)
	{
		$table = $this->wrapTable($blueprint);
		$on = $this->wrapTable($command->on);
		$columns = $this->columnize($command->columns);
		$onColumns = $this->columnize((array) $command->references);
		$sql = "alter table {$table} add constraint {$command->index} ";
		$sql .= "foreign key ({$columns}) references {$on} ({$onColumns})";
		if ( ! is_null($command->onDelete))
		{
			$sql .= " on delete {$command->onDelete}";
		}
		if ( ! is_null($command->onUpdate))
		{
			$sql .= " on update {$command->onUpdate}";
		}
		return $sql;
	}
	protected function getColumns(Blueprint $blueprint)
	{
		$columns = array();
		foreach ($blueprint->getAddedColumns() as $column)
		{
			$sql = $this->wrap($column).' '.$this->getType($column);
			$columns[] = $this->addModifiers($sql, $blueprint, $column);
		}
		return $columns;
	}
	protected function addModifiers($sql, Blueprint $blueprint, Fluent $column)
	{
		foreach ($this->modifiers as $modifier)
		{
			if (method_exists($this, $method = "modify{$modifier}"))
			{
				$sql .= $this->{$method}($blueprint, $column);
			}
		}
		return $sql;
	}
	protected function getCommandByName(Blueprint $blueprint, $name)
	{
		$commands = $this->getCommandsByName($blueprint, $name);
		if (count($commands) > 0)
		{
			return reset($commands);
		}
	}
	protected function getCommandsByName(Blueprint $blueprint, $name)
	{
		return array_filter($blueprint->getCommands(), function($value) use ($name)
		{
			return $value->name == $name;
		});
	}
	protected function getType(Fluent $column)
	{
		return $this->{"type".ucfirst($column->type)}($column);
	}
	public function prefixArray($prefix, array $values)
	{
		return array_map(function($value) use ($prefix)
		{
			return $prefix.' '.$value;
		}, $values);
	}
	public function wrapTable($table)
	{
		if ($table instanceof Blueprint) $table = $table->getTable();
		return parent::wrapTable($table);
	}
	public function wrap($value, $prefixAlias = false)
	{
		if ($value instanceof Fluent) $value = $value->name;
		return parent::wrap($value, $prefixAlias);
	}
	protected function getDefaultValue($value)
	{
		if ($value instanceof Expression) return $value;
		if (is_bool($value)) return "'".(int) $value."'";
		return "'".strval($value)."'";
	}
	protected function getDoctrineTableDiff(Blueprint $blueprint, SchemaManager $schema)
	{
		$table = $this->getTablePrefix().$blueprint->getTable();
		$tableDiff = new TableDiff($table);
		$tableDiff->fromTable = $schema->listTableDetails($table);
		return $tableDiff;
	}
	public function compileChange(Blueprint $blueprint, Fluent $command, Connection $connection)
	{
		$schema = $connection->getDoctrineSchemaManager();
		$tableDiff = $this->getChangedDiff($blueprint, $schema);
		if ($tableDiff !== false)
		{
			return (array) $schema->getDatabasePlatform()->getAlterTableSQL($tableDiff);
		}
		return [];
	}
	protected function getChangedDiff(Blueprint $blueprint, SchemaManager $schema)
	{
		$table = $schema->listTableDetails($this->getTablePrefix().$blueprint->getTable());
		return (new Comparator)->diffTable($table, $this->getTableWithColumnChanges($blueprint, $table));
	}
	protected function getTableWithColumnChanges(Blueprint $blueprint, Table $table)
	{
		$table = clone $table;
		foreach($blueprint->getChangedColumns() as $fluent)
		{
			$column = $this->getDoctrineColumnForChange($table, $fluent);
			foreach ($fluent->getAttributes() as $key => $value)
			{
				if ( ! is_null($option = $this->mapFluentOptionToDoctrine($key)))
				{
					if (method_exists($column, $method = 'set'.ucfirst($option)))
					{
						$column->{$method}($this->mapFluentValueToDoctrine($option, $value));
					}
				}
			}
		}
		return $table;
	}
	protected function getDoctrineColumnForChange(Table $table, Fluent $fluent)
	{
		return $table->changeColumn(
			$fluent['name'], $this->getDoctrineColumnChangeOptions($fluent)
		)->getColumn($fluent['name']);
	}
	protected function getDoctrineColumnChangeOptions(Fluent $fluent)
	{
		$options = ['type' => $this->getDoctrineColumnType($fluent['type'])];
		if (in_array($fluent['type'], ['text', 'mediumText', 'longText']))
		{
			$options['length'] = $this->calculateDoctrineTextLength($fluent['type']);
		}
		return $options;
	}
	protected function getDoctrineColumnType($type)
	{
		$type = strtolower($type);
		switch ($type) {
			case 'biginteger':
				$type = 'bigint';
				break;
			case 'smallinteger':
				$type = 'smallint';
				break;
			case 'mediumtext':
			case 'longtext':
				$type = 'text';
				break;
		}
		return Type::getType($type);
	}
	protected function calculateDoctrineTextLength($type)
	{
		switch ($type)
		{
			case 'mediumText':
				return 65535 + 1;
			case 'longText':
				return 16777215 + 1;
			default:
				return 255 + 1;
		}
	}
	protected function mapFluentOptionToDoctrine($attribute)
	{
		switch($attribute)
		{
			case 'type':
			case 'name':
				return;
			case 'nullable':
				return 'notnull';
			case 'total':
				return 'precision';
			case 'places':
				return 'scale';
			default:
				return $attribute;
		}
	}
	protected function mapFluentValueToDoctrine($option, $value)
	{
		return $option == 'notnull' ? ! $value : $value;
	}
}
