<?php namespace Illuminate\Database\Query\Processors;
class SQLiteProcessor extends Processor {
	public function processColumnListing($results)
	{
		$mapping = function($r)
		{
			$r = (object) $r;
			return $r->name;
		};
		return array_map($mapping, $results);
	}
}
