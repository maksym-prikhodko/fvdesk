<?php namespace Illuminate\Foundation\Support\Providers;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
class RouteServiceProvider extends ServiceProvider {
	protected $namespace;
	public function boot(Router $router)
	{
		$this->setRootControllerNamespace();
		if ($this->app->routesAreCached())
		{
			$this->loadCachedRoutes();
		}
		else
		{
			$this->loadRoutes();
		}
	}
	protected function setRootControllerNamespace()
	{
		if (is_null($this->namespace)) return;
		$this->app['Illuminate\Contracts\Routing\UrlGenerator']
						->setRootControllerNamespace($this->namespace);
	}
	protected function loadCachedRoutes()
	{
		$this->app->booted(function()
		{
			require $this->app->getCachedRoutesPath();
		});
	}
	protected function loadRoutes()
	{
		$this->app->call([$this, 'map']);
	}
	protected function loadRoutesFrom($path)
	{
		$router = $this->app['Illuminate\Routing\Router'];
		if (is_null($this->namespace)) return require $path;
		$router->group(['namespace' => $this->namespace], function($router) use ($path)
		{
			require $path;
		});
	}
	public function register()
	{
	}
	public function __call($method, $parameters)
	{
		return call_user_func_array([$this->app['router'], $method], $parameters);
	}
}
