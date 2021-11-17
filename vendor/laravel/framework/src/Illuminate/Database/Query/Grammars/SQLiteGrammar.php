<?php namespace Illuminate\Database\Query\Grammars;
use Illuminate\Database\Query\Builder;
class SQLiteGrammar extends Grammar {
	protected $operators = array(
		'=', '<', '>', '<=', '>=', '<>', '!=',
		'like', 'not like', 'between', 'ilike',
		'&', '|', '<<', '>>',
	);
	public function compileInsert(Builder $query, array $values)
	{
		$table = $this->wrapTable($query->from);
		if ( ! is_array(reset($values)))
		{
			$values = array($values);
		}
		if (count($values) == 1)
		{
			return parent::compileInsert($query, reset($values));
		}
		$names = $this->columnize(array_keys(reset($values)));
		$columns = array();
		foreach (array_keys(reset($values)) as $column)
		{
			$columns[] = '? as '.$this->wrap($column);
		}
		$columns = array_fill(0, count($values), implode(', ', $columns));
		return "insert into $table ($names) select ".implode(' union all select ', $columns);
	}
	public function compileTruncate(Builder $query)
	{
		$sql = array('delete from sqlite_sequence where name = ?' => array($query->from));
		$sql['delete from '.$this->wrapTable($query->from)] = array();
		return $sql;
	}
	protected function whereDay(Builder $query, $where)
	{
		return $this->dateBasedWhere('%d', $query, $where);
	}
	protected function whereMonth(Builder $query, $where)
	{
		return $this->dateBasedWhere('%m', $query, $where);
	}
	protected function whereYear(Builder $query, $where)
	{
		return $this->dateBasedWhere('%Y', $query, $where);
	}
	protected function dateBasedWhere($type, Builder $query, $where)
	{
		$value = str_pad($where['value'], 2, '0', STR_PAD_LEFT);
		$value = $this->parameter($value);
		return 'strftime(\''.$type.'\', '.$this->wrap($where['column']).') '.$where['operator'].' '.$value;
	}
}
