<?php namespace Illuminate\Contracts\Routing;
use Closure;
interface Middleware {
	public function handle($request, Closure $next);
}
