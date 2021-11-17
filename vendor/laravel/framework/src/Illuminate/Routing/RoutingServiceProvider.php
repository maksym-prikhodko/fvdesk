<?php namespace Illuminate\Routing;
use Illuminate\Support\ServiceProvider;
class RoutingServiceProvider extends ServiceProvider {
	public function register()
	{
		$this->registerRouter();
		$this->registerUrlGenerator();
		$this->registerRedirector();
		$this->registerResponseFactory();
	}
	protected function registerRouter()
	{
		$this->app['router'] = $this->app->share(function($app)
		{
			return new Router($app['events'], $app);
		});
	}
	protected function registerUrlGenerator()
	{
		$this->app['url'] = $this->app->share(function($app)
		{
			$routes = $app['router']->getRoutes();
			$app->instance('routes', $routes);
			$url = new UrlGenerator(
				$routes, $app->rebinding(
					'request', $this->requestRebinder()
				)
			);
			$url->setSessionResolver(function()
			{
				return $this->app['session'];
			});
			$app->rebinding('routes', function($app, $routes)
			{
				$app['url']->setRoutes($routes);
			});
			return $url;
		});
	}
	protected function requestRebinder()
	{
		return function($app, $request)
		{
			$app['url']->setRequest($request);
		};
	}
	protected function registerRedirector()
	{
		$this->app['redirect'] = $this->app->share(function($app)
		{
			$redirector = new Redirector($app['url']);
			if (isset($app['session.store']))
			{
				$redirector->setSession($app['session.store']);
			}
			return $redirector;
		});
	}
	protected function registerResponseFactory()
	{
		$this->app->singleton('Illuminate\Contracts\Routing\ResponseFactory', function($app)
		{
			return new ResponseFactory($app['Illuminate\Contracts\View\Factory'], $app['redirect']);
		});
	}
}
