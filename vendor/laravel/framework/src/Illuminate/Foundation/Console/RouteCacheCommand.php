<?php namespace Illuminate\Foundation\Console;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Routing\RouteCollection;
class RouteCacheCommand extends Command {
	protected $name = 'route:cache';
	protected $description = 'Create a route cache file for faster route registration';
	protected $files;
	public function __construct(Filesystem $files)
	{
		parent::__construct();
		$this->files = $files;
	}
	public function fire()
	{
		$this->call('route:clear');
		$routes = $this->getFreshApplicationRoutes();
		if (count($routes) == 0)
		{
			return $this->error("Your application doesn't have any routes.");
		}
		foreach ($routes as $route)
		{
			$route->prepareForSerialization();
		}
		$this->files->put(
			$this->laravel->getCachedRoutesPath(), $this->buildRouteCacheFile($routes)
		);
		$this->info('Routes cached successfully!');
	}
	protected function getFreshApplicationRoutes()
	{
		$app = require $this->laravel->basePath().'/bootstrap/app.php';
		$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
		return $app['router']->getRoutes();
	}
	protected function buildRouteCacheFile(RouteCollection $routes)
	{
		$stub = $this->files->get(__DIR__.'/stubs/routes.stub');
		return str_replace('{{routes}}', base64_encode(serialize($routes)), $stub);
	}
}
