<?php namespace Illuminate\Html;
use Illuminate\Support\ServiceProvider;
class HtmlServiceProvider extends ServiceProvider {
	protected $defer = true;
	public function register()
	{
		$this->registerHtmlBuilder();
		$this->registerFormBuilder();
		$this->app->alias('html', 'Illuminate\Html\HtmlBuilder');
		$this->app->alias('form', 'Illuminate\Html\FormBuilder');
	}
	protected function registerHtmlBuilder()
	{
		$this->app->bindShared('html', function($app)
		{
			return new HtmlBuilder($app['url']);
		});
	}
	protected function registerFormBuilder()
	{
		$this->app->bindShared('form', function($app)
		{
			$form = new FormBuilder($app['html'], $app['url'], $app['session.store']->getToken());
			return $form->setSessionStore($app['session.store']);
		});
	}
	public function provides()
	{
		return array('html', 'form');
	}
}
