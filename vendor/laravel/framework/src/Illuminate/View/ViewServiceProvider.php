<?php namespace Illuminate\View;
use Illuminate\View\Engines\PhpEngine;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\Engines\CompilerEngine;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\View\Compilers\BladeCompiler;
class ViewServiceProvider extends ServiceProvider {
	public function register()
	{
		$this->registerEngineResolver();
		$this->registerViewFinder();
		$this->registerFactory();
	}
	public function registerEngineResolver()
	{
		$this->app->singleton('view.engine.resolver', function()
		{
			$resolver = new EngineResolver;
			foreach (array('php', 'blade') as $engine)
			{
				$this->{'register'.ucfirst($engine).'Engine'}($resolver);
			}
			return $resolver;
		});
	}
	public function registerPhpEngine($resolver)
	{
		$resolver->register('php', function() { return new PhpEngine; });
	}
	public function registerBladeEngine($resolver)
	{
		$app = $this->app;
		$app->singleton('blade.compiler', function($app)
		{
			$cache = $app['config']['view.compiled'];
			return new BladeCompiler($app['files'], $cache);
		});
		$resolver->register('blade', function() use ($app)
		{
			return new CompilerEngine($app['blade.compiler'], $app['files']);
		});
	}
	public function registerViewFinder()
	{
		$this->app->bind('view.finder', function($app)
		{
			$paths = $app['config']['view.paths'];
			return new FileViewFinder($app['files'], $paths);
		});
	}
	public function registerFactory()
	{
		$this->app->singleton('view', function($app)
		{
			$resolver = $app['view.engine.resolver'];
			$finder = $app['view.finder'];
			$env = new Factory($resolver, $finder, $app['events']);
			$env->setContainer($app);
			$env->share('app', $app);
			return $env;
		});
	}
}
