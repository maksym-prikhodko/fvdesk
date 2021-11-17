<?php namespace Illuminate\Foundation\Http\Middleware;
use Closure;
use Illuminate\Contracts\Routing\Middleware;
use Illuminate\Contracts\Foundation\Application;
use Symfony\Component\HttpKernel\Exception\HttpException;
class CheckForMaintenanceMode implements Middleware {
	protected $app;
	public function __construct(Application $app)
	{
		$this->app = $app;
	}
	public function handle($request, Closure $next)
	{
		if ($this->app->isDownForMaintenance())
		{
			throw new HttpException(503);
		}
		return $next($request);
	}
}
