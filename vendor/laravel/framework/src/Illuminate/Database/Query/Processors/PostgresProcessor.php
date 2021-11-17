<?php namespace Illuminate\Database\Query\Processors;
use Illuminate\Database\Query\Builder;
class PostgresProcessor extends Processor {
	public function processInsertGetId(Builder $query, $sql, $values, $sequence = null)
	{
		$results = $query->getConnection()->selectFromWriteConnection($sql, $values);
		$sequence = $sequence ?: 'id';
		$result = (array) $results[0];
		$id = $result[$sequence];
		return is_numeric($id) ? (int) $id : $id;
	}
	public function processColumnListing($results)
	{
		$mapping = function($r)
		{
			$r = (object) $r;
			return $r->column_name;
		};
		return array_map($mapping, $results);
	}
}
