<?php namespace Illuminate\Foundation\Bootstrap;
use Illuminate\Http\Request;
use Illuminate\Contracts\Foundation\Application;
class SetRequestForConsole {
	public function bootstrap(Application $app)
	{
		$url = $app['config']->get('app.url', 'http:
		$app->instance('request', Request::create($url, 'GET', [], [], [], $_SERVER));
	}
}
