<?php namespace Illuminate\Database\Schema;
use Closure;
use Illuminate\Support\Fluent;
use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Grammars\Grammar;
class Blueprint {
	protected $table;
	protected $columns = array();
	protected $commands = array();
	public $engine;
	public function __construct($table, Closure $callback = null)
	{
		$this->table = $table;
		if ( ! is_null($callback)) $callback($this);
	}
	public function build(Connection $connection, Grammar $grammar)
	{
		foreach ($this->toSql($connection, $grammar) as $statement)
		{
			$connection->statement($statement);
		}
	}
	public function toSql(Connection $connection, Grammar $grammar)
	{
		$this->addImpliedCommands();
		$statements = array();
		foreach ($this->commands as $command)
		{
			$method = 'compile'.ucfirst($command->name);
			if (method_exists($grammar, $method))
			{
				if ( ! is_null($sql = $grammar->$method($this, $command, $connection)))
				{
					$statements = array_merge($statements, (array) $sql);
				}
			}
		}
		return $statements;
	}
	protected function addImpliedCommands()
	{
		if (count($this->getAddedColumns()) > 0 && ! $this->creating())
		{
			array_unshift($this->commands, $this->createCommand('add'));
		}
		if (count($this->getChangedColumns()) > 0 && ! $this->creating())
		{
			array_unshift($this->commands, $this->createCommand('change'));
		}
		$this->addFluentIndexes();
	}
	protected function addFluentIndexes()
	{
		foreach ($this->columns as $column)
		{
			foreach (array('primary', 'unique', 'index') as $index)
			{
				if ($column->$index === true)
				{
					$this->$index($column->name);
					continue 2;
				}
				elseif (isset($column->$index))
				{
					$this->$index($column->name, $column->$index);
					continue 2;
				}
			}
		}
	}
	protected function creating()
	{
		foreach ($this->commands as $command)
		{
			if ($command->name == 'create') return true;
		}
		return false;
	}
	public function create()
	{
		return $this->addCommand('create');
	}
	public function drop()
	{
		return $this->addCommand('drop');
	}
	public function dropIfExists()
	{
		return $this->addCommand('dropIfExists');
	}
	public function dropColumn($columns)
	{
		$columns = is_array($columns) ? $columns : (array) func_get_args();
		return $this->addCommand('dropColumn', compact('columns'));
	}
	public function renameColumn($from, $to)
	{
		return $this->addCommand('renameColumn', compact('from', 'to'));
	}
	public function dropPrimary($index = null)
	{
		return $this->dropIndexCommand('dropPrimary', 'primary', $index);
	}
	public function dropUnique($index)
	{
		return $this->dropIndexCommand('dropUnique', 'unique', $index);
	}
	public function dropIndex($index)
	{
		return $this->dropIndexCommand('dropIndex', 'index', $index);
	}
	public function dropForeign($index)
	{
		return $this->dropIndexCommand('dropForeign', 'foreign', $index);
	}
	public function dropTimestamps()
	{
		$this->dropColumn('created_at', 'updated_at');
	}
	public function dropSoftDeletes()
	{
		$this->dropColumn('deleted_at');
	}
	public function rename($to)
	{
		return $this->addCommand('rename', compact('to'));
	}
	public function primary($columns, $name = null)
	{
		return $this->indexCommand('primary', $columns, $name);
	}
	public function unique($columns, $name = null)
	{
		return $this->indexCommand('unique', $columns, $name);
	}
	public function index($columns, $name = null)
	{
		return $this->indexCommand('index', $columns, $name);
	}
	public function foreign($columns, $name = null)
	{
		return $this->indexCommand('foreign', $columns, $name);
	}
	public function increments($column)
	{
		return $this->unsignedInteger($column, true);
	}
	public function bigIncrements($column)
	{
		return $this->unsignedBigInteger($column, true);
	}
	public function char($column, $length = 255)
	{
		return $this->addColumn('char', $column, compact('length'));
	}
	public function string($column, $length = 255)
	{
		return $this->addColumn('string', $column, compact('length'));
	}
	public function text($column)
	{
		return $this->addColumn('text', $column);
	}
	public function mediumText($column)
	{
		return $this->addColumn('mediumText', $column);
	}
	public function longText($column)
	{
		return $this->addColumn('longText', $column);
	}
	public function integer($column, $autoIncrement = false, $unsigned = false)
	{
		return $this->addColumn('integer', $column, compact('autoIncrement', 'unsigned'));
	}
	public function bigInteger($column, $autoIncrement = false, $unsigned = false)
	{
		return $this->addColumn('bigInteger', $column, compact('autoIncrement', 'unsigned'));
	}
	public function mediumInteger($column, $autoIncrement = false, $unsigned = false)
	{
		return $this->addColumn('mediumInteger', $column, compact('autoIncrement', 'unsigned'));
	}
	public function tinyInteger($column, $autoIncrement = false, $unsigned = false)
	{
		return $this->addColumn('tinyInteger', $column, compact('autoIncrement', 'unsigned'));
	}
	public function smallInteger($column, $autoIncrement = false, $unsigned = false)
	{
		return $this->addColumn('smallInteger', $column, compact('autoIncrement', 'unsigned'));
	}
	public function unsignedInteger($column, $autoIncrement = false)
	{
		return $this->integer($column, $autoIncrement, true);
	}
	public function unsignedBigInteger($column, $autoIncrement = false)
	{
		return $this->bigInteger($column, $autoIncrement, true);
	}
	public function float($column, $total = 8, $places = 2)
	{
		return $this->addColumn('float', $column, compact('total', 'places'));
	}
	public function double($column, $total = null, $places = null)
	{
		return $this->addColumn('double', $column, compact('total', 'places'));
	}
	public function decimal($column, $total = 8, $places = 2)
	{
		return $this->addColumn('decimal', $column, compact('total', 'places'));
	}
	public function boolean($column)
	{
		return $this->addColumn('boolean', $column);
	}
	public function enum($column, array $allowed)
	{
		return $this->addColumn('enum', $column, compact('allowed'));
	}
	public function json($column)
	{
		return $this->addColumn('json', $column);
	}
	public function jsonb($column)
	{
		return $this->addColumn('jsonb', $column);
	}
	public function date($column)
	{
		return $this->addColumn('date', $column);
	}
	public function dateTime($column)
	{
		return $this->addColumn('dateTime', $column);
	}
	public function dateTimeTz($column)
	{
		return $this->addColumn('dateTimeTz', $column);
	}
	public function time($column)
	{
		return $this->addColumn('time', $column);
	}
	public function timeTz($column)
	{
		return $this->addColumn('timeTz', $column);
	}
	public function timestamp($column)
	{
		return $this->addColumn('timestamp', $column);
	}
	public function timestampTz($column)
	{
		return $this->addColumn('timestampTz', $column);
	}
	public function nullableTimestamps()
	{
		$this->timestamp('created_at')->nullable();
		$this->timestamp('updated_at')->nullable();
	}
	public function timestamps()
	{
		$this->timestamp('created_at');
		$this->timestamp('updated_at');
	}
	public function softDeletes()
	{
		return $this->timestamp('deleted_at')->nullable();
	}
	public function binary($column)
	{
		return $this->addColumn('binary', $column);
	}
	public function morphs($name, $indexName = null)
	{
		$this->unsignedInteger("{$name}_id");
		$this->string("{$name}_type");
		$this->index(array("{$name}_id", "{$name}_type"), $indexName);
	}
	public function rememberToken()
	{
		return $this->string('remember_token', 100)->nullable();
	}
	protected function dropIndexCommand($command, $type, $index)
	{
		$columns = array();
		if (is_array($index))
		{
			$columns = $index;
			$index = $this->createIndexName($type, $columns);
		}
		return $this->indexCommand($command, $columns, $index);
	}
	protected function indexCommand($type, $columns, $index)
	{
		$columns = (array) $columns;
		if (is_null($index))
		{
			$index = $this->createIndexName($type, $columns);
		}
		return $this->addCommand($type, compact('index', 'columns'));
	}
	protected function createIndexName($type, array $columns)
	{
		$index = strtolower($this->table.'_'.implode('_', $columns).'_'.$type);
		return str_replace(array('-', '.'), '_', $index);
	}
	protected function addColumn($type, $name, array $parameters = array())
	{
		$attributes = array_merge(compact('type', 'name'), $parameters);
		$this->columns[] = $column = new Fluent($attributes);
		return $column;
	}
	public function removeColumn($name)
	{
		$this->columns = array_values(array_filter($this->columns, function($c) use ($name)
		{
			return $c['attributes']['name'] != $name;
		}));
		return $this;
	}
	protected function addCommand($name, array $parameters = array())
	{
		$this->commands[] = $command = $this->createCommand($name, $parameters);
		return $command;
	}
	protected function createCommand($name, array $parameters = array())
	{
		return new Fluent(array_merge(compact('name'), $parameters));
	}
	public function getTable()
	{
		return $this->table;
	}
	public function getColumns()
	{
		return $this->columns;
	}
	public function getCommands()
	{
		return $this->commands;
	}
	public function getAddedColumns()
	{
		return array_filter($this->columns, function($column)
		{
			return !$column->change;
		});
	}
	public function getChangedColumns()
	{
		return array_filter($this->columns, function($column)
		{
			return !!$column->change;
		});
	}
}
