<?php namespace Illuminate\Contracts\Console;
interface Kernel {
	public function handle($input, $output = null);
	public function call($command, array $parameters = array());
	public function queue($command, array $parameters = array());
	public function all();
	public function output();
}
