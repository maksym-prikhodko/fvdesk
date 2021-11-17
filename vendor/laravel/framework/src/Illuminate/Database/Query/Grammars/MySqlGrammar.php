<?php namespace Illuminate\Database\Query\Grammars;
use Illuminate\Database\Query\Builder;
class MySqlGrammar extends Grammar {
	protected $selectComponents = array(
		'aggregate',
		'columns',
		'from',
		'joins',
		'wheres',
		'groups',
		'havings',
		'orders',
		'limit',
		'offset',
		'lock',
	);
	public function compileSelect(Builder $query)
	{
		$sql = parent::compileSelect($query);
		if ($query->unions)
		{
			$sql = '('.$sql.') '.$this->compileUnions($query);
		}
		return $sql;
	}
	protected function compileUnion(array $union)
	{
		$joiner = $union['all'] ? ' union all ' : ' union ';
		return $joiner.'('.$union['query']->toSql().')';
	}
	protected function compileLock(Builder $query, $value)
	{
		if (is_string($value)) return $value;
		return $value ? 'for update' : 'lock in share mode';
	}
	public function compileUpdate(Builder $query, $values)
	{
		$sql = parent::compileUpdate($query, $values);
		if (isset($query->orders))
		{
			$sql .= ' '.$this->compileOrders($query, $query->orders);
		}
		if (isset($query->limit))
		{
			$sql .= ' '.$this->compileLimit($query, $query->limit);
		}
		return rtrim($sql);
	}
	public function compileDelete(Builder $query)
	{
		$table = $this->wrapTable($query->from);
		$where = is_array($query->wheres) ? $this->compileWheres($query) : '';
		if (isset($query->joins))
		{
			$joins = ' '.$this->compileJoins($query, $query->joins);
			$sql = trim("delete $table from {$table}{$joins} $where");
		}
		else
		{
			$sql = trim("delete from $table $where");
		}
		if (isset($query->orders))
		{
			$sql .= ' '.$this->compileOrders($query, $query->orders);
		}
		if (isset($query->limit))
		{
			$sql .= ' '.$this->compileLimit($query, $query->limit);
		}
		return $sql;
	}
	protected function wrapValue($value)
	{
		if ($value === '*') return $value;
		return '`'.str_replace('`', '``', $value).'`';
	}
}
