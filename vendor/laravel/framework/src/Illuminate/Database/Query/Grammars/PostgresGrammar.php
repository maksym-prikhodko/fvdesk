<?php namespace Illuminate\Database\Query\Grammars;
use Illuminate\Database\Query\Builder;
class PostgresGrammar extends Grammar {
	protected $operators = array(
		'=', '<', '>', '<=', '>=', '<>', '!=',
		'like', 'not like', 'between', 'ilike',
		'&', '|', '#', '<<', '>>',
	);
	protected function compileLock(Builder $query, $value)
	{
		if (is_string($value)) return $value;
		return $value ? 'for update' : 'for share';
	}
	public function compileUpdate(Builder $query, $values)
	{
		$table = $this->wrapTable($query->from);
		$columns = $this->compileUpdateColumns($values);
		$from = $this->compileUpdateFrom($query);
		$where = $this->compileUpdateWheres($query);
		return trim("update {$table} set {$columns}{$from} $where");
	}
	protected function compileUpdateColumns($values)
	{
		$columns = array();
		foreach ($values as $key => $value)
		{
			$columns[] = $this->wrap($key).' = '.$this->parameter($value);
		}
		return implode(', ', $columns);
	}
	protected function compileUpdateFrom(Builder $query)
	{
		if ( ! isset($query->joins)) return '';
		$froms = array();
		foreach ($query->joins as $join)
		{
			$froms[] = $this->wrapTable($join->table);
		}
		if (count($froms) > 0) return ' from '.implode(', ', $froms);
	}
	protected function compileUpdateWheres(Builder $query)
	{
		$baseWhere = $this->compileWheres($query);
		if ( ! isset($query->joins)) return $baseWhere;
		$joinWhere = $this->compileUpdateJoinWheres($query);
		if (trim($baseWhere) == '')
		{
			return 'where '.$this->removeLeadingBoolean($joinWhere);
		}
		return $baseWhere.' '.$joinWhere;
	}
	protected function compileUpdateJoinWheres(Builder $query)
	{
		$joinWheres = array();
		foreach ($query->joins as $join)
		{
			foreach ($join->clauses as $clause)
			{
				$joinWheres[] = $this->compileJoinConstraint($clause);
			}
		}
		return implode(' ', $joinWheres);
	}
	public function compileInsertGetId(Builder $query, $values, $sequence)
	{
		if (is_null($sequence)) $sequence = 'id';
		return $this->compileInsert($query, $values).' returning '.$this->wrap($sequence);
	}
	public function compileTruncate(Builder $query)
	{
		return array('truncate '.$this->wrapTable($query->from).' restart identity' => array());
	}
}
