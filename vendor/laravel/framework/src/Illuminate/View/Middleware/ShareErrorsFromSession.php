<?php namespace Illuminate\View\Middleware;
use Closure;
use Illuminate\Support\ViewErrorBag;
use Illuminate\Contracts\Routing\Middleware;
use Illuminate\Contracts\View\Factory as ViewFactory;
class ShareErrorsFromSession implements Middleware {
	protected $view;
	public function __construct(ViewFactory $view)
	{
		$this->view = $view;
	}
	public function handle($request, Closure $next)
	{
		if ($request->session()->has('errors'))
		{
			$this->view->share(
				'errors', $request->session()->get('errors')
			);
		}
		else
		{
			$this->view->share('errors', new ViewErrorBag);
		}
		return $next($request);
	}
}
