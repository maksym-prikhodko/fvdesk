<?php namespace Illuminate\Session;
use SessionHandlerInterface;
use Illuminate\Database\ConnectionInterface;
class DatabaseSessionHandler implements SessionHandlerInterface, ExistenceAwareInterface {
	protected $connection;
	protected $table;
	protected $exists;
	public function __construct(ConnectionInterface $connection, $table)
	{
		$this->table = $table;
		$this->connection = $connection;
	}
	public function open($savePath, $sessionName)
	{
		return true;
	}
	public function close()
	{
		return true;
	}
	public function read($sessionId)
	{
		$session = (object) $this->getQuery()->find($sessionId);
		if (isset($session->payload))
		{
			$this->exists = true;
			return base64_decode($session->payload);
		}
	}
	public function write($sessionId, $data)
	{
		if ($this->exists)
		{
			$this->getQuery()->where('id', $sessionId)->update([
				'payload' => base64_encode($data), 'last_activity' => time(),
			]);
		}
		else
		{
			$this->getQuery()->insert([
				'id' => $sessionId, 'payload' => base64_encode($data), 'last_activity' => time(),
			]);
		}
		$this->exists = true;
	}
	public function destroy($sessionId)
	{
		$this->getQuery()->where('id', $sessionId)->delete();
	}
	public function gc($lifetime)
	{
		$this->getQuery()->where('last_activity', '<=', time() - $lifetime)->delete();
	}
	protected function getQuery()
	{
		return $this->connection->table($this->table);
	}
	public function setExists($value)
	{
		$this->exists = $value;
		return $this;
	}
}
