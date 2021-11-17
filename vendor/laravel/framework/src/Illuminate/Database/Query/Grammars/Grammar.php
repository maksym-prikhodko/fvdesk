<?php namespace Illuminate\Database\Query\Grammars;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Grammar as BaseGrammar;
class Grammar extends BaseGrammar {
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
		'unions',
		'lock',
	);
	public function compileSelect(Builder $query)
	{
		if (is_null($query->columns)) $query->columns = array('*');
		return trim($this->concatenate($this->compileComponents($query)));
	}
	protected function compileComponents(Builder $query)
	{
		$sql = array();
		foreach ($this->selectComponents as $component)
		{
			if ( ! is_null($query->$component))
			{
				$method = 'compile'.ucfirst($component);
				$sql[$component] = $this->$method($query, $query->$component);
			}
		}
		return $sql;
	}
	protected function compileAggregate(Builder $query, $aggregate)
	{
		$column = $this->columnize($aggregate['columns']);
		if ($query->distinct && $column !== '*')
		{
			$column = 'distinct '.$column;
		}
		return 'select '.$aggregate['function'].'('.$column.') as aggregate';
	}
	protected function compileColumns(Builder $query, $columns)
	{
		if ( ! is_null($query->aggregate)) return;
		$select = $query->distinct ? 'select distinct ' : 'select ';
		return $select.$this->columnize($columns);
	}
	protected function compileFrom(Builder $query, $table)
	{
		return 'from '.$this->wrapTable($table);
	}
	protected function compileJoins(Builder $query, $joins)
	{
		$sql = array();
		$query->setBindings(array(), 'join');
		foreach ($joins as $join)
		{
			$table = $this->wrapTable($join->table);
			$clauses = array();
			foreach ($join->clauses as $clause)
			{
				$clauses[] = $this->compileJoinConstraint($clause);
			}
			foreach ($join->bindings as $binding)
			{
				$query->addBinding($binding, 'join');
			}
			$clauses[0] = $this->removeLeadingBoolean($clauses[0]);
			$clauses = implode(' ', $clauses);
			$type = $join->type;
			$sql[] = "$type join $table on $clauses";
		}
		return implode(' ', $sql);
	}
	protected function compileJoinConstraint(array $clause)
	{
		$first = $this->wrap($clause['first']);
		$second = $clause['where'] ? '?' : $this->wrap($clause['second']);
		return "{$clause['boolean']} $first {$clause['operator']} $second";
	}
	protected function compileWheres(Builder $query)
	{
		$sql = array();
		if (is_null($query->wheres)) return '';
		foreach ($query->wheres as $where)
		{
			$method = "where{$where['type']}";
			$sql[] = $where['boolean'].' '.$this->$method($query, $where);
		}
		if (count($sql) > 0)
		{
			$sql = implode(' ', $sql);
			return 'where '.$this->removeLeadingBoolean($sql);
		}
		return '';
	}
	protected function whereNested(Builder $query, $where)
	{
		$nested = $where['query'];
		return '('.substr($this->compileWheres($nested), 6).')';
	}
	protected function whereSub(Builder $query, $where)
	{
		$select = $this->compileSelect($where['query']);
		return $this->wrap($where['column']).' '.$where['operator']." ($select)";
	}
	protected function whereBasic(Builder $query, $where)
	{
		$value = $this->parameter($where['value']);
		return $this->wrap($where['column']).' '.$where['operator'].' '.$value;
	}
	protected function whereBetween(Builder $query, $where)
	{
		$between = $where['not'] ? 'not between' : 'between';
		return $this->wrap($where['column']).' '.$between.' ? and ?';
	}
	protected function whereExists(Builder $query, $where)
	{
		return 'exists ('.$this->compileSelect($where['query']).')';
	}
	protected function whereNotExists(Builder $query, $where)
	{
		return 'not exists ('.$this->compileSelect($where['query']).')';
	}
	protected function whereIn(Builder $query, $where)
	{
		if (empty($where['values'])) return '0 = 1';
		$values = $this->parameterize($where['values']);
		return $this->wrap($where['column']).' in ('.$values.')';
	}
	protected function whereNotIn(Builder $query, $where)
	{
		if (empty($where['values'])) return '1 = 1';
		$values = $this->parameterize($where['values']);
		return $this->wrap($where['column']).' not in ('.$values.')';
	}
	protected function whereInSub(Builder $query, $where)
	{
		$select = $this->compileSelect($where['query']);
		return $this->wrap($where['column']).' in ('.$select.')';
	}
	protected function whereNotInSub(Builder $query, $where)
	{
		$select = $this->compileSelect($where['query']);
		return $this->wrap($where['column']).' not in ('.$select.')';
	}
	protected function whereNull(Builder $query, $where)
	{
		return $this->wrap($where['column']).' is null';
	}
	protected function whereNotNull(Builder $query, $where)
	{
		return $this->wrap($where['column']).' is not null';
	}
	protected function whereDate(Builder $query, $where)
	{
		return $this->dateBasedWhere('date', $query, $where);
	}
	protected function whereDay(Builder $query, $where)
	{
		return $this->dateBasedWhere('day', $query, $where);
	}
	protected function whereMonth(Builder $query, $where)
	{
		return $this->dateBasedWhere('month', $query, $where);
	}
	protected function whereYear(Builder $query, $where)
	{
		return $this->dateBasedWhere('year', $query, $where);
	}
	protected function dateBasedWhere($type, Builder $query, $where)
	{
		$value = $this->parameter($where['value']);
		return $type.'('.$this->wrap($where['column']).') '.$where['operator'].' '.$value;
	}
	protected function whereRaw(Builder $query, $where)
	{
		return $where['sql'];
	}
	protected function compileGroups(Builder $query, $groups)
	{
		return 'group by '.$this->columnize($groups);
	}
	protected function compileHavings(Builder $query, $havings)
	{
		$sql = implode(' ', array_map(array($this, 'compileHaving'), $havings));
		return 'having '.$this->removeLeadingBoolean($sql);
	}
	protected function compileHaving(array $having)
	{
		if ($having['type'] === 'raw')
		{
			return $having['boolean'].' '.$having['sql'];
		}
		return $this->compileBasicHaving($having);
	}
	protected function compileBasicHaving($having)
	{
		$column = $this->wrap($having['column']);
		$parameter = $this->parameter($having['value']);
		return $having['boolean'].' '.$column.' '.$having['operator'].' '.$parameter;
	}
	protected function compileOrders(Builder $query, $orders)
	{
		return 'order by '.implode(', ', array_map(function($order)
		{
			if (isset($order['sql'])) return $order['sql'];
			return $this->wrap($order['column']).' '.$order['direction'];
		}, $orders));
	}
	protected function compileLimit(Builder $query, $limit)
	{
		return 'limit '.(int) $limit;
	}
	protected function compileOffset(Builder $query, $offset)
	{
		return 'offset '.(int) $offset;
	}
	protected function compileUnions(Builder $query)
	{
		$sql = '';
		foreach ($query->unions as $union)
		{
			$sql .= $this->compileUnion($union);
		}
		if (isset($query->unionOrders))
		{
			$sql .= ' '.$this->compileOrders($query, $query->unionOrders);
		}
		if (isset($query->unionLimit))
		{
			$sql .= ' '.$this->compileLimit($query, $query->unionLimit);
		}
		if (isset($query->unionOffset))
		{
			$sql .= ' '.$this->compileOffset($query, $query->unionOffset);
		}
		return ltrim($sql);
	}
	protected function compileUnion(array $union)
	{
		$joiner = $union['all'] ? ' union all ' : ' union ';
		return $joiner.$union['query']->toSql();
	}
	public function compileInsert(Builder $query, array $values)
	{
		$table = $this->wrapTable($query->from);
		if ( ! is_array(reset($values)))
		{
			$values = array($values);
		}
		$columns = $this->columnize(array_keys(reset($values)));
		$parameters = $this->parameterize(reset($values));
		$value = array_fill(0, count($values), "($parameters)");
		$parameters = implode(', ', $value);
		return "insert into $table ($columns) values $parameters";
	}
	public function compileInsertGetId(Builder $query, $values, $sequence)
	{
		return $this->compileInsert($query, $values);
	}
	public function compileUpdate(Builder $query, $values)
	{
		$table = $this->wrapTable($query->from);
		$columns = array();
		foreach ($values as $key => $value)
		{
			$columns[] = $this->wrap($key).' = '.$this->parameter($value);
		}
		$columns = implode(', ', $columns);
		if (isset($query->joins))
		{
			$joins = ' '.$this->compileJoins($query, $query->joins);
		}
		else
		{
			$joins = '';
		}
		$where = $this->compileWheres($query);
		return trim("update {$table}{$joins} set $columns $where");
	}
	public function compileDelete(Builder $query)
	{
		$table = $this->wrapTable($query->from);
		$where = is_array($query->wheres) ? $this->compileWheres($query) : '';
		return trim("delete from $table ".$where);
	}
	public function compileTruncate(Builder $query)
	{
		return array('truncate '.$this->wrapTable($query->from) => array());
	}
	protected function compileLock(Builder $query, $value)
	{
		return is_string($value) ? $value : '';
	}
	protected function concatenate($segments)
	{
		return implode(' ', array_filter($segments, function($value)
		{
			return (string) $value !== '';
		}));
	}
	protected function removeLeadingBoolean($value)
	{
		return preg_replace('/and |or /', '', $value, 1);
	}
}
