<?php namespace Illuminate\Foundation\Testing;
use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\Authenticatable as UserContract;
trait ApplicationTrait {
	protected $app;
	protected $response;
	protected $code;
	protected function refreshApplication()
	{
		putenv('APP_ENV=testing');
		$this->app = $this->createApplication();
	}
	public function call($method, $uri, $parameters = [], $cookies = [], $files = [], $server = [], $content = null)
	{
		$request = Request::create($uri, $method, $parameters, $cookies, $files, $server, $content);
		return $this->response = $this->app->make('Illuminate\Contracts\Http\Kernel')->handle($request);
	}
	public function callSecure($method, $uri, $parameters = [], $cookies = [], $files = [], $server = [], $content = null)
	{
		$uri = 'https:
		return $this->response = $this->call($method, $uri, $parameters, $cookies, $files, $server, $content);
	}
	public function action($method, $action, $wildcards = [], $parameters = [], $cookies = [], $files = [], $server = [], $content = null)
	{
		$uri = $this->app['url']->action($action, $wildcards, true);
		return $this->response = $this->call($method, $uri, $parameters, $cookies, $files, $server, $content);
	}
	public function route($method, $name, $routeParameters = [], $parameters = [], $cookies = [], $files = [], $server = [], $content = null)
	{
		$uri = $this->app['url']->route($name, $routeParameters);
		return $this->response = $this->call($method, $uri, $parameters, $cookies, $files, $server, $content);
	}
	public function session(array $data)
	{
		$this->startSession();
		foreach ($data as $key => $value)
		{
			$this->app['session']->put($key, $value);
		}
	}
	public function flushSession()
	{
		$this->startSession();
		$this->app['session']->flush();
	}
	protected function startSession()
	{
		if ( ! $this->app['session']->isStarted())
		{
			$this->app['session']->start();
		}
	}
	public function be(UserContract $user, $driver = null)
	{
		$this->app['auth']->driver($driver)->setUser($user);
	}
	public function seed($class = 'DatabaseSeeder')
	{
		$this->artisan('db:seed', ['--class' => $class]);
	}
	public function artisan($command, $parameters = [])
	{
		return $this->code = $this->app['Illuminate\Contracts\Console\Kernel']->call($command, $parameters);
	}
}
