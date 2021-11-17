<?php namespace Illuminate\Validation;
use Illuminate\Database\ConnectionResolverInterface;
class DatabasePresenceVerifier implements PresenceVerifierInterface {
	protected $db;
	protected $connection = null;
	public function __construct(ConnectionResolverInterface $db)
	{
		$this->db = $db;
	}
	public function getCount($collection, $column, $value, $excludeId = null, $idColumn = null, array $extra = array())
	{
		$query = $this->table($collection)->where($column, '=', $value);
		if ( ! is_null($excludeId) && $excludeId != 'NULL')
		{
			$query->where($idColumn ?: 'id', '<>', $excludeId);
		}
		foreach ($extra as $key => $extraValue)
		{
			$this->addWhere($query, $key, $extraValue);
		}
		return $query->count();
	}
	public function getMultiCount($collection, $column, array $values, array $extra = array())
	{
		$query = $this->table($collection)->whereIn($column, $values);
		foreach ($extra as $key => $extraValue)
		{
			$this->addWhere($query, $key, $extraValue);
		}
		return $query->count();
	}
	protected function addWhere($query, $key, $extraValue)
	{
		if ($extraValue === 'NULL')
		{
			$query->whereNull($key);
		}
		elseif ($extraValue === 'NOT_NULL')
		{
			$query->whereNotNull($key);
		}
		else
		{
			$query->where($key, $extraValue);
		}
	}
	protected function table($table)
	{
		return $this->db->connection($this->connection)->table($table);
	}
	public function setConnection($connection)
	{
		$this->connection = $connection;
	}
}
