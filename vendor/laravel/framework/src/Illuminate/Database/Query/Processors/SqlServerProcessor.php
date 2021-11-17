<?php namespace Illuminate\Database\Query\Processors;
use Illuminate\Database\Query\Builder;
class SqlServerProcessor extends Processor {
	public function processInsertGetId(Builder $query, $sql, $values, $sequence = null)
	{
		$query->getConnection()->insert($sql, $values);
		$id = $query->getConnection()->getPdo()->lastInsertId();
		return is_numeric($id) ? (int) $id : $id;
	}
	public function processColumnListing($results)
	{
		$mapping = function($r)
		{
			return $r->name;
		};
		return array_map($mapping, $results);
	}
}
