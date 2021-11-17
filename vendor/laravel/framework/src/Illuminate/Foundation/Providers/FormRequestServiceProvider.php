<?php namespace Illuminate\Foundation\Providers;
use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Request;
class FormRequestServiceProvider extends ServiceProvider {
	public function register()
	{
	}
	public function boot()
	{
		$this->app['events']->listen('router.matched', function()
		{
			$this->app->resolving(function(FormRequest $request, $app)
			{
				$this->initializeRequest($request, $app['request']);
				$request->setContainer($app)
                        ->setRedirector($app['Illuminate\Routing\Redirector']);
			});
		});
	}
	protected function initializeRequest(FormRequest $form, Request $current)
	{
		$files = $current->files->all();
		$files = is_array($files) ? array_filter($files) : $files;
		$form->initialize(
			$current->query->all(), $current->request->all(), $current->attributes->all(),
			$current->cookies->all(), $files, $current->server->all(), $current->getContent()
		);
		if ($session = $current->getSession())
		{
			$form->setSession($session);
		}
		$form->setUserResolver($current->getUserResolver());
		$form->setRouteResolver($current->getRouteResolver());
	}
}
