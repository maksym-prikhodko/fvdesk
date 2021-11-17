<?php namespace Illuminate\Foundation\Bootstrap;
use Illuminate\Support\Facades\Facade;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Contracts\Foundation\Application;
class RegisterFacades {
	public function bootstrap(Application $app)
	{
		Facade::clearResolvedInstances();
		Facade::setFacadeApplication($app);
		AliasLoader::getInstance($app['config']['app.aliases'])->register();
	}
}
