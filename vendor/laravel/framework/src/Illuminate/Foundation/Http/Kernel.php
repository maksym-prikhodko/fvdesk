<?php namespace Illuminate\Foundation\Http;
use Exception;
use Illuminate\Routing\Router;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\Facade;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\TerminableMiddleware;
use Illuminate\Contracts\Http\Kernel as KernelContract;
class Kernel implements KernelContract {
	protected $app;
	protected $router;
	protected $bootstrappers = [
		'Illuminate\Foundation\Bootstrap\DetectEnvironment',
		'Illuminate\Foundation\Bootstrap\LoadConfiguration',
		'Illuminate\Foundation\Bootstrap\ConfigureLogging',
		'Illuminate\Foundation\Bootstrap\HandleExceptions',
		'Illuminate\Foundation\Bootstrap\RegisterFacades',
		'Illuminate\Foundation\Bootstrap\RegisterProviders',
		'Illuminate\Foundation\Bootstrap\BootProviders',
	];
	protected $middleware = [];
	protected $routeMiddleware = [];
	public function __construct(Application $app, Router $router)
	{
		$this->app = $app;
		$this->router = $router;
		foreach ($this->routeMiddleware as $key => $middleware)
		{
			$router->middleware($key, $middleware);
		}
	}
	public function handle($request)
	{
		try
		{
			$response = $this->sendRequestThroughRouter($request);
		}
		catch (Exception $e)
		{
			$this->reportException($e);
			$response = $this->renderException($request, $e);
		}
		$this->app['events']->fire('kernel.handled', [$request, $response]);
		return $response;
	}
	protected function sendRequestThroughRouter($request)
	{
		$this->app->instance('request', $request);
		Facade::clearResolvedInstance('request');
		$this->bootstrap();
		return (new Pipeline($this->app))
		            ->send($request)
		            ->through($this->middleware)
		            ->then($this->dispatchToRouter());
	}
	public function terminate($request, $response)
	{
		$routeMiddlewares = $this->gatherRouteMiddlewares($request);
		foreach (array_merge($routeMiddlewares, $this->middleware) as $middleware)
		{
			$instance = $this->app->make($middleware);
			if ($instance instanceof TerminableMiddleware)
			{
				$instance->terminate($request, $response);
			}
		}
		$this->app->terminate();
	}
	protected function gatherRouteMiddlewares($request)
	{
		if ($request->route())
		{
			return $this->router->gatherRouteMiddlewares($request->route());
		}
		return [];
	}
	public function prependMiddleware($middleware)
	{
		if (array_search($middleware, $this->middleware) === false)
		{
			array_unshift($this->middleware, $middleware);
		}
		return $this;
	}
	public function pushMiddleware($middleware)
	{
		if (array_search($middleware, $this->middleware) === false)
		{
			$this->middleware[] = $middleware;
		}
		return $this;
	}
	public function bootstrap()
	{
		if ( ! $this->app->hasBeenBootstrapped())
		{
			$this->app->bootstrapWith($this->bootstrappers());
		}
	}
	protected function dispatchToRouter()
	{
		return function($request)
		{
			$this->app->instance('request', $request);
			return $this->router->dispatch($request);
		};
	}
	protected function bootstrappers()
	{
		return $this->bootstrappers;
	}
	protected function reportException(Exception $e)
	{
		$this->app['Illuminate\Contracts\Debug\ExceptionHandler']->report($e);
	}
	protected function renderException($request, Exception $e)
	{
		return $this->app['Illuminate\Contracts\Debug\ExceptionHandler']->render($request, $e);
	}
	public function getApplication()
	{
		return $this->app;
	}
}
