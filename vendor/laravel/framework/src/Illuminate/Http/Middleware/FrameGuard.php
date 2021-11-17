<?php namespace Illuminate\Http\Middleware;
use Closure;
use Illuminate\Contracts\Routing\Middleware;
class FrameGuard implements Middleware {
	public function handle($request, Closure $next)
	{
		$response = $next($request);
		$response->headers->set('X-Frame-Options', 'SAMEORIGIN', false);
		return $response;
	}
}
