<?php namespace Illuminate\Database\Query\Grammars;
use Illuminate\Database\Query\Builder;
class SqlServerGrammar extends Grammar {
	protected $operators = array(
		'=', '<', '>', '<=', '>=', '!<', '!>', '<>', '!=',
		'like', 'not like', 'between', 'ilike',
		'&', '&=', '|', '|=', '^', '^=',
	);
	public function compileSelect(Builder $query)
	{
		$components = $this->compileComponents($query);
		if ($query->offset > 0)
		{
			return $this->compileAnsiOffset($query, $components);
		}
		return $this->concatenate($components);
	}
	protected function compileColumns(Builder $query, $columns)
	{
		if ( ! is_null($query->aggregate)) return;
		$select = $query->distinct ? 'select distinct ' : 'select ';
		if ($query->limit > 0 && $query->offset <= 0)
		{
			$select .= 'top '.$query->limit.' ';
		}
		return $select.$this->columnize($columns);
	}
	protected function compileFrom(Builder $query, $table)
	{
		$from = parent::compileFrom($query, $table);
		if (is_string($query->lock)) return $from.' '.$query->lock;
		if ( ! is_null($query->lock))
		{
			return $from.' with(rowlock,'.($query->lock ? 'updlock,' : '').'holdlock)';
		}
		return $from;
	}
	protected function compileAnsiOffset(Builder $query, $components)
	{
		if ( ! isset($components['orders']))
		{
			$components['orders'] = 'order by (select 0)';
		}
		$orderings = $components['orders'];
		$components['columns'] .= $this->compileOver($orderings);
		unset($components['orders']);
		$constraint = $this->compileRowConstraint($query);
		$sql = $this->concatenate($components);
		return $this->compileTableExpression($sql, $constraint);
	}
	protected function compileOver($orderings)
	{
		return ", row_number() over ({$orderings}) as row_num";
	}
	protected function compileRowConstraint($query)
	{
		$start = $query->offset + 1;
		if ($query->limit > 0)
		{
			$finish = $query->offset + $query->limit;
			return "between {$start} and {$finish}";
		}
		return ">= {$start}";
	}
	protected function compileTableExpression($sql, $constraint)
	{
		return "select * from ({$sql}) as temp_table where row_num {$constraint}";
	}
	protected function compileLimit(Builder $query, $limit)
	{
		return '';
	}
	protected function compileOffset(Builder $query, $offset)
	{
		return '';
	}
	public function compileTruncate(Builder $query)
	{
		return array('truncate table '.$this->wrapTable($query->from) => array());
	}
	public function getDateFormat()
	{
		return 'Y-m-d H:i:s.000';
	}
	protected function wrapValue($value)
	{
		if ($value === '*') return $value;
		return '['.str_replace(']', ']]', $value).']';
	}
}
