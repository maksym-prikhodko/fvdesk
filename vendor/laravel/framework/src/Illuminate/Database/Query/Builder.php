<?php namespace Illuminate\Database\Query;
use Closure;
use BadMethodCallException;
use InvalidArgumentException;
use Illuminate\Support\Collection;
use Illuminate\Pagination\Paginator;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Query\Grammars\Grammar;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Processors\Processor;
class Builder {
	protected $connection;
	protected $grammar;
	protected $processor;
	protected $bindings = array(
		'select' => [],
		'join'   => [],
		'where'  => [],
		'having' => [],
		'order'  => [],
	);
	public $aggregate;
	public $columns;
	public $distinct = false;
	public $from;
	public $joins;
	public $wheres;
	public $groups;
	public $havings;
	public $orders;
	public $limit;
	public $offset;
	public $unions;
	public $unionLimit;
	public $unionOffset;
	public $unionOrders;
	public $lock;
	protected $backups = [];
	protected $operators = array(
		'=', '<', '>', '<=', '>=', '<>', '!=',
		'like', 'like binary', 'not like', 'between', 'ilike',
		'&', '|', '^', '<<', '>>',
		'rlike', 'regexp', 'not regexp',
		'~', '~*', '!~', '!~*', 'similar to',
                'not similar to',
	);
	protected $useWritePdo = false;
	public function __construct(ConnectionInterface $connection,
                                Grammar $grammar,
                                Processor $processor)
	{
		$this->grammar = $grammar;
		$this->processor = $processor;
		$this->connection = $connection;
	}
	public function select($columns = array('*'))
	{
		$this->columns = is_array($columns) ? $columns : func_get_args();
		return $this;
	}
	public function selectRaw($expression, array $bindings = array())
	{
		$this->addSelect(new Expression($expression));
		if ($bindings)
		{
			$this->addBinding($bindings, 'select');
		}
		return $this;
	}
	public function selectSub($query, $as)
	{
		if ($query instanceof Closure)
		{
			$callback = $query;
			$callback($query = $this->newQuery());
		}
		if ($query instanceof Builder)
		{
			$bindings = $query->getBindings();
			$query = $query->toSql();
		}
		elseif (is_string($query))
		{
			$bindings = [];
		}
		else
		{
			throw new InvalidArgumentException;
		}
		return $this->selectRaw('('.$query.') as '.$this->grammar->wrap($as), $bindings);
	}
	public function addSelect($column)
	{
		$column = is_array($column) ? $column : func_get_args();
		$this->columns = array_merge((array) $this->columns, $column);
		return $this;
	}
	public function distinct()
	{
		$this->distinct = true;
		return $this;
	}
	public function from($table)
	{
		$this->from = $table;
		return $this;
	}
	public function join($table, $one, $operator = null, $two = null, $type = 'inner', $where = false)
	{
		if ($one instanceof Closure)
		{
			$this->joins[] = new JoinClause($type, $table);
			call_user_func($one, end($this->joins));
		}
		else
		{
			$join = new JoinClause($type, $table);
			$this->joins[] = $join->on(
				$one, $operator, $two, 'and', $where
			);
		}
		return $this;
	}
	public function joinWhere($table, $one, $operator, $two, $type = 'inner')
	{
		return $this->join($table, $one, $operator, $two, $type, true);
	}
	public function leftJoin($table, $first, $operator = null, $second = null)
	{
		return $this->join($table, $first, $operator, $second, 'left');
	}
	public function leftJoinWhere($table, $one, $operator, $two)
	{
		return $this->joinWhere($table, $one, $operator, $two, 'left');
	}
	public function rightJoin($table, $first, $operator = null, $second = null)
	{
		return $this->join($table, $first, $operator, $second, 'right');
	}
	public function rightJoinWhere($table, $one, $operator, $two)
	{
		return $this->joinWhere($table, $one, $operator, $two, 'right');
	}
	public function where($column, $operator = null, $value = null, $boolean = 'and')
	{
		if (is_array($column))
		{
			return $this->whereNested(function($query) use ($column)
			{
				foreach ($column as $key => $value)
				{
					$query->where($key, '=', $value);
				}
			}, $boolean);
		}
		if (func_num_args() == 2)
		{
			list($value, $operator) = array($operator, '=');
		}
		elseif ($this->invalidOperatorAndValue($operator, $value))
		{
			throw new InvalidArgumentException("Illegal operator and value combination.");
		}
		if ($column instanceof Closure)
		{
			return $this->whereNested($column, $boolean);
		}
		if ( ! in_array(strtolower($operator), $this->operators, true))
		{
			list($value, $operator) = array($operator, '=');
		}
		if ($value instanceof Closure)
		{
			return $this->whereSub($column, $operator, $value, $boolean);
		}
		if (is_null($value))
		{
			return $this->whereNull($column, $boolean, $operator != '=');
		}
		$type = 'Basic';
		$this->wheres[] = compact('type', 'column', 'operator', 'value', 'boolean');
		if ( ! $value instanceof Expression)
		{
			$this->addBinding($value, 'where');
		}
		return $this;
	}
	public function orWhere($column, $operator = null, $value = null)
	{
		return $this->where($column, $operator, $value, 'or');
	}
	protected function invalidOperatorAndValue($operator, $value)
	{
		$isOperator = in_array($operator, $this->operators);
		return $isOperator && $operator != '=' && is_null($value);
	}
	public function whereRaw($sql, array $bindings = array(), $boolean = 'and')
	{
		$type = 'raw';
		$this->wheres[] = compact('type', 'sql', 'boolean');
		$this->addBinding($bindings, 'where');
		return $this;
	}
	public function orWhereRaw($sql, array $bindings = array())
	{
		return $this->whereRaw($sql, $bindings, 'or');
	}
	public function whereBetween($column, array $values, $boolean = 'and', $not = false)
	{
		$type = 'between';
		$this->wheres[] = compact('column', 'type', 'boolean', 'not');
		$this->addBinding($values, 'where');
		return $this;
	}
	public function orWhereBetween($column, array $values)
	{
		return $this->whereBetween($column, $values, 'or');
	}
	public function whereNotBetween($column, array $values, $boolean = 'and')
	{
		return $this->whereBetween($column, $values, $boolean, true);
	}
	public function orWhereNotBetween($column, array $values)
	{
		return $this->whereNotBetween($column, $values, 'or');
	}
	public function whereNested(Closure $callback, $boolean = 'and')
	{
		$query = $this->newQuery();
		$query->from($this->from);
		call_user_func($callback, $query);
		return $this->addNestedWhereQuery($query, $boolean);
	}
	public function addNestedWhereQuery($query, $boolean = 'and')
	{
		if (count($query->wheres))
		{
			$type = 'Nested';
			$this->wheres[] = compact('type', 'query', 'boolean');
			$this->mergeBindings($query);
		}
		return $this;
	}
	protected function whereSub($column, $operator, Closure $callback, $boolean)
	{
		$type = 'Sub';
		$query = $this->newQuery();
		call_user_func($callback, $query);
		$this->wheres[] = compact('type', 'column', 'operator', 'query', 'boolean');
		$this->mergeBindings($query);
		return $this;
	}
	public function whereExists(Closure $callback, $boolean = 'and', $not = false)
	{
		$type = $not ? 'NotExists' : 'Exists';
		$query = $this->newQuery();
		call_user_func($callback, $query);
		$this->wheres[] = compact('type', 'operator', 'query', 'boolean');
		$this->mergeBindings($query);
		return $this;
	}
	public function orWhereExists(Closure $callback, $not = false)
	{
		return $this->whereExists($callback, 'or', $not);
	}
	public function whereNotExists(Closure $callback, $boolean = 'and')
	{
		return $this->whereExists($callback, $boolean, true);
	}
	public function orWhereNotExists(Closure $callback)
	{
		return $this->orWhereExists($callback, true);
	}
	public function whereIn($column, $values, $boolean = 'and', $not = false)
	{
		$type = $not ? 'NotIn' : 'In';
		if ($values instanceof Closure)
		{
			return $this->whereInSub($column, $values, $boolean, $not);
		}
		$this->wheres[] = compact('type', 'column', 'values', 'boolean');
		$this->addBinding($values, 'where');
		return $this;
	}
	public function orWhereIn($column, $values)
	{
		return $this->whereIn($column, $values, 'or');
	}
	public function whereNotIn($column, $values, $boolean = 'and')
	{
		return $this->whereIn($column, $values, $boolean, true);
	}
	public function orWhereNotIn($column, $values)
	{
		return $this->whereNotIn($column, $values, 'or');
	}
	protected function whereInSub($column, Closure $callback, $boolean, $not)
	{
		$type = $not ? 'NotInSub' : 'InSub';
		call_user_func($callback, $query = $this->newQuery());
		$this->wheres[] = compact('type', 'column', 'query', 'boolean');
		$this->mergeBindings($query);
		return $this;
	}
	public function whereNull($column, $boolean = 'and', $not = false)
	{
		$type = $not ? 'NotNull' : 'Null';
		$this->wheres[] = compact('type', 'column', 'boolean');
		return $this;
	}
	public function orWhereNull($column)
	{
		return $this->whereNull($column, 'or');
	}
	public function whereNotNull($column, $boolean = 'and')
	{
		return $this->whereNull($column, $boolean, true);
	}
	public function orWhereNotNull($column)
	{
		return $this->whereNotNull($column, 'or');
	}
	public function whereDate($column, $operator, $value, $boolean = 'and')
	{
		return $this->addDateBasedWhere('Date', $column, $operator, $value, $boolean);
	}
	public function whereDay($column, $operator, $value, $boolean = 'and')
	{
		return $this->addDateBasedWhere('Day', $column, $operator, $value, $boolean);
	}
	public function whereMonth($column, $operator, $value, $boolean = 'and')
	{
		return $this->addDateBasedWhere('Month', $column, $operator, $value, $boolean);
	}
	public function whereYear($column, $operator, $value, $boolean = 'and')
	{
		return $this->addDateBasedWhere('Year', $column, $operator, $value, $boolean);
	}
	protected function addDateBasedWhere($type, $column, $operator, $value, $boolean = 'and')
	{
		$this->wheres[] = compact('column', 'type', 'boolean', 'operator', 'value');
		$this->addBinding($value, 'where');
		return $this;
	}
	public function dynamicWhere($method, $parameters)
	{
		$finder = substr($method, 5);
		$segments = preg_split('/(And|Or)(?=[A-Z])/', $finder, -1, PREG_SPLIT_DELIM_CAPTURE);
		$connector = 'and';
		$index = 0;
		foreach ($segments as $segment)
		{
			if ($segment != 'And' && $segment != 'Or')
			{
				$this->addDynamic($segment, $connector, $parameters, $index);
				$index++;
			}
			else
			{
				$connector = $segment;
			}
		}
		return $this;
	}
	protected function addDynamic($segment, $connector, $parameters, $index)
	{
		$bool = strtolower($connector);
		$this->where(snake_case($segment), '=', $parameters[$index], $bool);
	}
	public function groupBy()
	{
		foreach (func_get_args() as $arg)
		{
			$this->groups = array_merge((array) $this->groups, is_array($arg) ? $arg : [$arg]);
		}
		return $this;
	}
	public function having($column, $operator = null, $value = null, $boolean = 'and')
	{
		$type = 'basic';
		$this->havings[] = compact('type', 'column', 'operator', 'value', 'boolean');
		if ( ! $value instanceof Expression)
		{
			$this->addBinding($value, 'having');
		}
		return $this;
	}
	public function orHaving($column, $operator = null, $value = null)
	{
		return $this->having($column, $operator, $value, 'or');
	}
	public function havingRaw($sql, array $bindings = array(), $boolean = 'and')
	{
		$type = 'raw';
		$this->havings[] = compact('type', 'sql', 'boolean');
		$this->addBinding($bindings, 'having');
		return $this;
	}
	public function orHavingRaw($sql, array $bindings = array())
	{
		return $this->havingRaw($sql, $bindings, 'or');
	}
	public function orderBy($column, $direction = 'asc')
	{
		$property = $this->unions ? 'unionOrders' : 'orders';
		$direction = strtolower($direction) == 'asc' ? 'asc' : 'desc';
		$this->{$property}[] = compact('column', 'direction');
		return $this;
	}
	public function latest($column = 'created_at')
	{
		return $this->orderBy($column, 'desc');
	}
	public function oldest($column = 'created_at')
	{
		return $this->orderBy($column, 'asc');
	}
	public function orderByRaw($sql, $bindings = array())
	{
		$type = 'raw';
		$this->orders[] = compact('type', 'sql');
		$this->addBinding($bindings, 'order');
		return $this;
	}
	public function offset($value)
	{
		$property = $this->unions ? 'unionOffset' : 'offset';
		$this->$property = max(0, $value);
		return $this;
	}
	public function skip($value)
	{
		return $this->offset($value);
	}
	public function limit($value)
	{
		$property = $this->unions ? 'unionLimit' : 'limit';
		if ($value > 0) $this->$property = $value;
		return $this;
	}
	public function take($value)
	{
		return $this->limit($value);
	}
	public function forPage($page, $perPage = 15)
	{
		return $this->skip(($page - 1) * $perPage)->take($perPage);
	}
	public function union($query, $all = false)
	{
		if ($query instanceof Closure)
		{
			call_user_func($query, $query = $this->newQuery());
		}
		$this->unions[] = compact('query', 'all');
		return $this->mergeBindings($query);
	}
	public function unionAll($query)
	{
		return $this->union($query, true);
	}
	public function lock($value = true)
	{
		$this->lock = $value;
		return $this;
	}
	public function lockForUpdate()
	{
		return $this->lock(true);
	}
	public function sharedLock()
	{
		return $this->lock(false);
	}
	public function toSql()
	{
		return $this->grammar->compileSelect($this);
	}
	public function find($id, $columns = array('*'))
	{
		return $this->where('id', '=', $id)->first($columns);
	}
	public function pluck($column)
	{
		$result = (array) $this->first(array($column));
		return count($result) > 0 ? reset($result) : null;
	}
	public function first($columns = array('*'))
	{
		$results = $this->take(1)->get($columns);
		return count($results) > 0 ? reset($results) : null;
	}
	public function get($columns = array('*'))
	{
		return $this->getFresh($columns);
	}
	public function getFresh($columns = array('*'))
	{
		if (is_null($this->columns)) $this->columns = $columns;
		return $this->processor->processSelect($this, $this->runSelect());
	}
	protected function runSelect()
	{
		return $this->connection->select($this->toSql(), $this->getBindings(), ! $this->useWritePdo);
	}
	public function paginate($perPage = 15, $columns = ['*'])
	{
		$page = Paginator::resolveCurrentPage();
		$total = $this->getCountForPagination();
		$results = $this->forPage($page, $perPage)->get($columns);
		return new LengthAwarePaginator($results, $total, $perPage, $page, [
			'path' => Paginator::resolveCurrentPath(),
		]);
	}
	public function simplePaginate($perPage = 15, $columns = ['*'])
	{
		$page = Paginator::resolveCurrentPage();
		$this->skip(($page - 1) * $perPage)->take($perPage + 1);
		return new Paginator($this->get($columns), $perPage, $page, [
			'path' => Paginator::resolveCurrentPath(),
		]);
	}
	public function getCountForPagination()
	{
		$this->backupFieldsForCount();
		$total = $this->count();
		$this->restoreFieldsForCount();
		return $total;
	}
	protected function backupFieldsForCount()
	{
		foreach (['orders', 'limit', 'offset'] as $field)
		{
			$this->backups[$field] = $this->{$field};
			$this->{$field} = null;
		}
	}
	protected function restoreFieldsForCount()
	{
		foreach (['orders', 'limit', 'offset'] as $field)
		{
			$this->{$field} = $this->backups[$field];
		}
		$this->backups = [];
	}
	public function chunk($count, callable $callback)
	{
		$results = $this->forPage($page = 1, $count)->get();
		while (count($results) > 0)
		{
			if (call_user_func($callback, $results) === false)
			{
				break;
			}
			$page++;
			$results = $this->forPage($page, $count)->get();
		}
	}
	public function lists($column, $key = null)
	{
		$columns = $this->getListSelect($column, $key);
		$results = new Collection($this->get($columns));
		return $results->lists($columns[0], array_get($columns, 1));
	}
	protected function getListSelect($column, $key)
	{
		$select = is_null($key) ? array($column) : array($column, $key);
		return array_map(function($column)
		{
			$dot = strpos($column, '.');
			return $dot === false ? $column : substr($column, $dot + 1);
		}, $select);
	}
	public function implode($column, $glue = null)
	{
		if (is_null($glue)) return implode($this->lists($column));
		return implode($glue, $this->lists($column));
	}
	public function exists()
	{
		$limit = $this->limit;
		$result = $this->limit(1)->count() > 0;
		$this->limit($limit);
		return $result;
	}
	public function count($columns = '*')
	{
		if ( ! is_array($columns))
		{
			$columns = array($columns);
		}
		return (int) $this->aggregate(__FUNCTION__, $columns);
	}
	public function min($column)
	{
		return $this->aggregate(__FUNCTION__, array($column));
	}
	public function max($column)
	{
		return $this->aggregate(__FUNCTION__, array($column));
	}
	public function sum($column)
	{
		$result = $this->aggregate(__FUNCTION__, array($column));
		return $result ?: 0;
	}
	public function avg($column)
	{
		return $this->aggregate(__FUNCTION__, array($column));
	}
	public function aggregate($function, $columns = array('*'))
	{
		$this->aggregate = compact('function', 'columns');
		$previousColumns = $this->columns;
		$results = $this->get($columns);
		$this->aggregate = null;
		$this->columns = $previousColumns;
		if (isset($results[0]))
		{
			$result = array_change_key_case((array) $results[0]);
			return $result['aggregate'];
		}
	}
	public function insert(array $values)
	{
		if (empty($values)) return true;
		if ( ! is_array(reset($values)))
		{
			$values = array($values);
		}
		else
		{
			foreach ($values as $key => $value)
			{
				ksort($value); $values[$key] = $value;
			}
		}
		$bindings = array();
		foreach ($values as $record)
		{
			foreach ($record as $value)
			{
				$bindings[] = $value;
			}
		}
		$sql = $this->grammar->compileInsert($this, $values);
		$bindings = $this->cleanBindings($bindings);
		return $this->connection->insert($sql, $bindings);
	}
	public function insertGetId(array $values, $sequence = null)
	{
		$sql = $this->grammar->compileInsertGetId($this, $values, $sequence);
		$values = $this->cleanBindings($values);
		return $this->processor->processInsertGetId($this, $sql, $values, $sequence);
	}
	public function update(array $values)
	{
		$bindings = array_values(array_merge($values, $this->getBindings()));
		$sql = $this->grammar->compileUpdate($this, $values);
		return $this->connection->update($sql, $this->cleanBindings($bindings));
	}
	public function increment($column, $amount = 1, array $extra = array())
	{
		$wrapped = $this->grammar->wrap($column);
		$columns = array_merge(array($column => $this->raw("$wrapped + $amount")), $extra);
		return $this->update($columns);
	}
	public function decrement($column, $amount = 1, array $extra = array())
	{
		$wrapped = $this->grammar->wrap($column);
		$columns = array_merge(array($column => $this->raw("$wrapped - $amount")), $extra);
		return $this->update($columns);
	}
	public function delete($id = null)
	{
		if ( ! is_null($id)) $this->where('id', '=', $id);
		$sql = $this->grammar->compileDelete($this);
		return $this->connection->delete($sql, $this->getBindings());
	}
	public function truncate()
	{
		foreach ($this->grammar->compileTruncate($this) as $sql => $bindings)
		{
			$this->connection->statement($sql, $bindings);
		}
	}
	public function newQuery()
	{
		return new Builder($this->connection, $this->grammar, $this->processor);
	}
	public function mergeWheres($wheres, $bindings)
	{
		$this->wheres = array_merge((array) $this->wheres, (array) $wheres);
		$this->bindings['where'] = array_values(array_merge($this->bindings['where'], (array) $bindings));
	}
	protected function cleanBindings(array $bindings)
	{
		return array_values(array_filter($bindings, function($binding)
		{
			return ! $binding instanceof Expression;
		}));
	}
	public function raw($value)
	{
		return $this->connection->raw($value);
	}
	public function getBindings()
	{
		return array_flatten($this->bindings);
	}
	public function getRawBindings()
	{
		return $this->bindings;
	}
	public function setBindings(array $bindings, $type = 'where')
	{
		if ( ! array_key_exists($type, $this->bindings))
		{
			throw new InvalidArgumentException("Invalid binding type: {$type}.");
		}
		$this->bindings[$type] = $bindings;
		return $this;
	}
	public function addBinding($value, $type = 'where')
	{
		if ( ! array_key_exists($type, $this->bindings))
		{
			throw new InvalidArgumentException("Invalid binding type: {$type}.");
		}
		if (is_array($value))
		{
			$this->bindings[$type] = array_values(array_merge($this->bindings[$type], $value));
		}
		else
		{
			$this->bindings[$type][] = $value;
		}
		return $this;
	}
	public function mergeBindings(Builder $query)
	{
		$this->bindings = array_merge_recursive($this->bindings, $query->bindings);
		return $this;
	}
	public function getConnection()
	{
		return $this->connection;
	}
	public function getProcessor()
	{
		return $this->processor;
	}
	public function getGrammar()
	{
		return $this->grammar;
	}
	public function useWritePdo()
	{
		$this->useWritePdo = true;
		return $this;
	}
	public function __call($method, $parameters)
	{
		if (starts_with($method, 'where'))
		{
			return $this->dynamicWhere($method, $parameters);
		}
		$className = get_class($this);
		throw new BadMethodCallException("Call to undefined method {$className}::{$method}()");
	}
}
