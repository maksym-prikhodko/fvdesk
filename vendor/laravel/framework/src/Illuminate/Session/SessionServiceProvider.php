<?php namespace Illuminate\Session;
use Illuminate\Support\ServiceProvider;
class SessionServiceProvider extends ServiceProvider {
	public function register()
	{
		$this->registerSessionManager();
		$this->registerSessionDriver();
		$this->app->singleton('Illuminate\Session\Middleware\StartSession');
	}
	protected function registerSessionManager()
	{
		$this->app->singleton('session', function($app)
		{
			return new SessionManager($app);
		});
	}
	protected function registerSessionDriver()
	{
		$this->app->singleton('session.store', function($app)
		{
			$manager = $app['session'];
			return $manager->driver();
		});
	}
}
