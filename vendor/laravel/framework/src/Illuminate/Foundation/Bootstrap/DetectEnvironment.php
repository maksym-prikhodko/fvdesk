<?php namespace Illuminate\Foundation\Bootstrap;
use Dotenv;
use InvalidArgumentException;
use Illuminate\Contracts\Foundation\Application;
class DetectEnvironment {
	public function bootstrap(Application $app)
	{
		try
		{
			Dotenv::load($app->basePath(), $app->environmentFile());
		}
		catch (InvalidArgumentException $e)
		{
		}
		$app->detectEnvironment(function()
		{
			return env('APP_ENV', 'production');
		});
	}
}
