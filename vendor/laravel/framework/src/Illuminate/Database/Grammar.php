<?php namespace Illuminate\Database;
use Illuminate\Database\Query\Expression;
abstract class Grammar {
	protected $tablePrefix = '';
	public function wrapArray(array $values)
	{
		return array_map(array($this, 'wrap'), $values);
	}
	public function wrapTable($table)
	{
		if ($this->isExpression($table)) return $this->getValue($table);
		return $this->wrap($this->tablePrefix.$table, true);
	}
	public function wrap($value, $prefixAlias = false)
	{
		if ($this->isExpression($value)) return $this->getValue($value);
		if (strpos(strtolower($value), ' as ') !== false)
		{
			$segments = explode(' ', $value);
			if ($prefixAlias) $segments[2] = $this->tablePrefix.$segments[2];
			return $this->wrap($segments[0]).' as '.$this->wrapValue($segments[2]);
		}
		$wrapped = array();
		$segments = explode('.', $value);
		foreach ($segments as $key => $segment)
		{
			if ($key == 0 && count($segments) > 1)
			{
				$wrapped[] = $this->wrapTable($segment);
			}
			else
			{
				$wrapped[] = $this->wrapValue($segment);
			}
		}
		return implode('.', $wrapped);
	}
	protected function wrapValue($value)
	{
		if ($value === '*') return $value;
		return '"'.str_replace('"', '""', $value).'"';
	}
	public function columnize(array $columns)
	{
		return implode(', ', array_map(array($this, 'wrap'), $columns));
	}
	public function parameterize(array $values)
	{
		return implode(', ', array_map(array($this, 'parameter'), $values));
	}
	public function parameter($value)
	{
		return $this->isExpression($value) ? $this->getValue($value) : '?';
	}
	public function getValue($expression)
	{
		return $expression->getValue();
	}
	public function isExpression($value)
	{
		return $value instanceof Expression;
	}
	public function getDateFormat()
	{
		return 'Y-m-d H:i:s';
	}
	public function getTablePrefix()
	{
		return $this->tablePrefix;
	}
	public function setTablePrefix($prefix)
	{
		$this->tablePrefix = $prefix;
		return $this;
	}
}
