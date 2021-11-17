<?php namespace Illuminate\Database;
use Closure;
interface ConnectionInterface {
	public function table($table);
	public function raw($value);
	public function selectOne($query, $bindings = array());
	public function select($query, $bindings = array());
	public function insert($query, $bindings = array());
	public function update($query, $bindings = array());
	public function delete($query, $bindings = array());
	public function statement($query, $bindings = array());
	public function affectingStatement($query, $bindings = array());
	public function unprepared($query);
	public function prepareBindings(array $bindings);
	public function transaction(Closure $callback);
	public function beginTransaction();
	public function commit();
	public function rollBack();
	public function transactionLevel();
	public function pretend(Closure $callback);
}
