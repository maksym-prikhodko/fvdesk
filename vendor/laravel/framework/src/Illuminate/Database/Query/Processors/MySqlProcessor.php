<?php namespace Illuminate\Database\Query\Processors;
class MySqlProcessor extends Processor {
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
