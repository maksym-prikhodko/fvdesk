<?php namespace Illuminate\Validation;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Validation\ValidatesWhenResolved;
class ValidationServiceProvider extends ServiceProvider {
	public function register()
	{
		$this->registerValidationResolverHook();
		$this->registerPresenceVerifier();
		$this->registerValidationFactory();
	}
	protected function registerValidationResolverHook()
	{
		$this->app->afterResolving(function(ValidatesWhenResolved $resolved)
		{
			$resolved->validate();
		});
	}
	protected function registerValidationFactory()
	{
		$this->app->singleton('validator', function($app)
		{
			$validator = new Factory($app['translator'], $app);
			if (isset($app['validation.presence']))
			{
				$validator->setPresenceVerifier($app['validation.presence']);
			}
			return $validator;
		});
	}
	protected function registerPresenceVerifier()
	{
		$this->app->singleton('validation.presence', function($app)
		{
			return new DatabasePresenceVerifier($app['db']);
		});
	}
}
