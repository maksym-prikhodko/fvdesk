<?php namespace Illuminate\Auth\Middleware;
use Closure;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Routing\Middleware;
class AuthenticateWithBasicAuth implements Middleware {
	protected $auth;
	public function __construct(Guard $auth)
	{
		$this->auth = $auth;
	}
	public function handle($request, Closure $next)
	{
		return $this->auth->basic() ?: $next($request);
	}
}
