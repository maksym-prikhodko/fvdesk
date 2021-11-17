<?php namespace Illuminate\Auth\Passwords;
use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Passwords\DatabaseTokenRepository as DbRepository;
class PasswordResetServiceProvider extends ServiceProvider {
	protected $defer = true;
	public function register()
	{
		$this->registerPasswordBroker();
		$this->registerTokenRepository();
	}
	protected function registerPasswordBroker()
	{
		$this->app->singleton('auth.password', function($app)
		{
			$tokens = $app['auth.password.tokens'];
			$users = $app['auth']->driver()->getProvider();
			$view = $app['config']['auth.password.email'];
			return new PasswordBroker(
				$tokens, $users, $app['mailer'], $view
			);
		});
	}
	protected function registerTokenRepository()
	{
		$this->app->singleton('auth.password.tokens', function($app)
		{
			$connection = $app['db']->connection();
			$table = $app['config']['auth.password.table'];
			$key = $app['config']['app.key'];
			$expire = $app['config']->get('auth.password.expire', 60);
			return new DbRepository($connection, $table, $key, $expire);
		});
	}
	public function provides()
	{
		return ['auth.password', 'auth.password.tokens'];
	}
}
