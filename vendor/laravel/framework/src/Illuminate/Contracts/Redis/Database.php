<?php namespace Illuminate\Contracts\Redis;
interface Database {
	public function command($method, array $parameters = array());
}
