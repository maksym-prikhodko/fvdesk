<?php namespace App\Http\Middleware;
use Closure;
class CheckRole {
	public function handle($request, Closure $next) {
		if ($request->user()->role == 'admin') {
			return $next($request);
		}
		return redirect('guest')->with('fails', 'You are not Autherised');
	}
}
