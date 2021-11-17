<?php namespace Illuminate\Auth;
use Illuminate\Support\Manager;
class AuthManager extends Manager {
	protected function createDriver($driver)
	{
		$guard = parent::createDriver($driver);
		$guard->setCookieJar($this->app['cookie']);
		$guard->setDispatcher($this->app['events']);
		return $guard->setRequest($this->app->refresh('request', $guard, 'setRequest'));
	}
	protected function callCustomCreator($driver)
	{
		$custom = parent::callCustomCreator($driver);
		if ($custom instanceof Guard) return $custom;
		return new Guard($custom, $this->app['session.store']);
	}
	public function createDatabaseDriver()
	{
		$provider = $this->createDatabaseProvider();
		return new Guard($provider, $this->app['session.store']);
	}
	protected function createDatabaseProvider()
	{
		$connection = $this->app['db']->connection();
		$table = $this->app['config']['auth.table'];
		return new DatabaseUserProvider($connection, $this->app['hash'], $table);
	}
	public function createEloquentDriver()
	{
		$provider = $this->createEloquentProvider();
		return new Guard($provider, $this->app['session.store']);
	}
	protected function createEloquentProvider()
	{
		$model = $this->app['config']['auth.model'];
		return new EloquentUserProvider($this->app['hash'], $model);
	}
	public function getDefaultDriver()
	{
		return $this->app['config']['auth.driver'];
	}
	public function setDefaultDriver($name)
	{
		$this->app['config']['auth.driver'] = $name;
	}
}
