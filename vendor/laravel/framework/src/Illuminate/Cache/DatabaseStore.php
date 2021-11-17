<?php namespace Illuminate\Cache;
use Exception;
use LogicException;
use Illuminate\Contracts\Cache\Store;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Contracts\Encryption\Encrypter as EncrypterContract;
class DatabaseStore implements Store {
	protected $connection;
	protected $encrypter;
	protected $table;
	protected $prefix;
	public function __construct(ConnectionInterface $connection, EncrypterContract $encrypter, $table, $prefix = '')
	{
		$this->table = $table;
		$this->prefix = $prefix;
		$this->encrypter = $encrypter;
		$this->connection = $connection;
	}
	public function get($key)
	{
		$prefixed = $this->prefix.$key;
		$cache = $this->table()->where('key', '=', $prefixed)->first();
		if ( ! is_null($cache))
		{
			if (is_array($cache)) $cache = (object) $cache;
			if (time() >= $cache->expiration)
			{
				$this->forget($key);
				return;
			}
			return $this->encrypter->decrypt($cache->value);
		}
	}
	public function put($key, $value, $minutes)
	{
		$key = $this->prefix.$key;
		$value = $this->encrypter->encrypt($value);
		$expiration = $this->getTime() + ($minutes * 60);
		try
		{
			$this->table()->insert(compact('key', 'value', 'expiration'));
		}
		catch (Exception $e)
		{
			$this->table()->where('key', '=', $key)->update(compact('value', 'expiration'));
		}
	}
	public function increment($key, $value = 1)
	{
		throw new LogicException("Increment operations not supported by this driver.");
	}
	public function decrement($key, $value = 1)
	{
		throw new LogicException("Decrement operations not supported by this driver.");
	}
	protected function getTime()
	{
		return time();
	}
	public function forever($key, $value)
	{
		$this->put($key, $value, 5256000);
	}
	public function forget($key)
	{
		$this->table()->where('key', '=', $this->prefix.$key)->delete();
		return true;
	}
	public function flush()
	{
		$this->table()->delete();
	}
	protected function table()
	{
		return $this->connection->table($this->table);
	}
	public function getConnection()
	{
		return $this->connection;
	}
	public function getEncrypter()
	{
		return $this->encrypter;
	}
	public function getPrefix()
	{
		return $this->prefix;
	}
}
