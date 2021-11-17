<?php namespace Illuminate\Session;
use Illuminate\Support\Manager;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\NullSessionHandler;
class SessionManager extends Manager {
	protected function callCustomCreator($driver)
	{
		return $this->buildSession(parent::callCustomCreator($driver));
	}
	protected function createArrayDriver()
	{
		return $this->buildSession(new NullSessionHandler);
	}
	protected function createCookieDriver()
	{
		$lifetime = $this->app['config']['session.lifetime'];
		return $this->buildSession(new CookieSessionHandler($this->app['cookie'], $lifetime));
	}
	protected function createFileDriver()
	{
		return $this->createNativeDriver();
	}
	protected function createNativeDriver()
	{
		$path = $this->app['config']['session.files'];
		return $this->buildSession(new FileSessionHandler($this->app['files'], $path));
	}
	protected function createDatabaseDriver()
	{
		$connection = $this->getDatabaseConnection();
		$table = $this->app['config']['session.table'];
		return $this->buildSession(new DatabaseSessionHandler($connection, $table));
	}
	protected function getDatabaseConnection()
	{
		$connection = $this->app['config']['session.connection'];
		return $this->app['db']->connection($connection);
	}
	protected function createApcDriver()
	{
		return $this->createCacheBased('apc');
	}
	protected function createMemcachedDriver()
	{
		return $this->createCacheBased('memcached');
	}
	protected function createWincacheDriver()
	{
		return $this->createCacheBased('wincache');
	}
	protected function createRedisDriver()
	{
		$handler = $this->createCacheHandler('redis');
		$handler->getCache()->getStore()->setConnection($this->app['config']['session.connection']);
		return $this->buildSession($handler);
	}
	protected function createCacheBased($driver)
	{
		return $this->buildSession($this->createCacheHandler($driver));
	}
	protected function createCacheHandler($driver)
	{
		$minutes = $this->app['config']['session.lifetime'];
		return new CacheBasedSessionHandler($this->app['cache']->driver($driver), $minutes);
	}
	protected function buildSession($handler)
	{
		if ($this->app['config']['session.encrypt'])
		{
			return new EncryptedStore(
				$this->app['config']['session.cookie'], $handler, $this->app['encrypter']
			);
		}
		else
		{
			return new Store($this->app['config']['session.cookie'], $handler);
		}
	}
	public function getSessionConfig()
	{
		return $this->app['config']['session'];
	}
	public function getDefaultDriver()
	{
		return $this->app['config']['session.driver'];
	}
	public function setDefaultDriver($name)
	{
		$this->app['config']['session.driver'] = $name;
	}
}
