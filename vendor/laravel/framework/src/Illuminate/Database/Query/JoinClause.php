<?php namespace Illuminate\Database\Query;
class JoinClause {
	public $type;
	public $table;
	public $clauses = array();
	public $bindings = array();
	public function __construct($type, $table)
	{
		$this->type = $type;
		$this->table = $table;
	}
	public function on($first, $operator, $second, $boolean = 'and', $where = false)
	{
		$this->clauses[] = compact('first', 'operator', 'second', 'boolean', 'where');
		if ($where) $this->bindings[] = $second;
		return $this;
	}
	public function orOn($first, $operator, $second)
	{
		return $this->on($first, $operator, $second, 'or');
	}
	public function where($first, $operator, $second, $boolean = 'and')
	{
		return $this->on($first, $operator, $second, $boolean, true);
	}
	public function orWhere($first, $operator, $second)
	{
		return $this->on($first, $operator, $second, 'or', true);
	}
	public function whereNull($column, $boolean = 'and')
	{
		return $this->on($column, 'is', new Expression('null'), $boolean, false);
	}
	public function orWhereNull($column)
	{
		return $this->whereNull($column, 'or');
	}
	public function whereNotNull($column, $boolean = 'and')
	{
		return $this->on($column, 'is', new Expression('not null'), $boolean, false);
	}
	public function orWhereNotNull($column)
	{
		return $this->whereNotNull($column, 'or');
	}
}
