<?php namespace Illuminate\Cache;
use Memcached;
use RuntimeException;
class MemcachedConnector {
	public function connect(array $servers)
	{
		$memcached = $this->getMemcached();
		foreach ($servers as $server)
		{
			$memcached->addServer(
				$server['host'], $server['port'], $server['weight']
			);
		}
		$memcachedStatus = $memcached->getVersion();
		if ( ! is_array($memcachedStatus))
		{
			throw new RuntimeException("No Memcached servers added.");
		}
		if (in_array('255.255.255', $memcachedStatus) && count(array_unique($memcachedStatus)) === 1)
		{
			throw new RuntimeException("Could not establish Memcached connection.");
		}
		return $memcached;
	}
	protected function getMemcached()
	{
		return new Memcached;
	}
}
