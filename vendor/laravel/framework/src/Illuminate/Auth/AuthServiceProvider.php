<?php namespace Illuminate\Auth;
use Illuminate\Support\ServiceProvider;
class AuthServiceProvider extends ServiceProvider {
	public function register()
	{
		$this->registerAuthenticator();
		$this->registerUserResolver();
		$this->registerRequestRebindHandler();
	}
	protected function registerAuthenticator()
	{
		$this->app->singleton('auth', function($app)
		{
			$app['auth.loaded'] = true;
			return new AuthManager($app);
		});
		$this->app->singleton('auth.driver', function($app)
		{
			return $app['auth']->driver();
		});
	}
	protected function registerUserResolver()
	{
		$this->app->bind('Illuminate\Contracts\Auth\Authenticatable', function($app)
		{
			return $app['auth']->user();
		});
	}
	protected function registerRequestRebindHandler()
	{
		$this->app->rebinding('request', function($app, $request)
		{
			$request->setUserResolver(function() use ($app)
			{
				return $app['auth']->user();
			});
		});
	}
}
