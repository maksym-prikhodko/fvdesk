<?php
namespace App\Http\Middleware;
use Closure;
class CheckRoleUser {
	public function handle($request, Closure $next) {
		if ($request->user()->role == 'user') {
			return $next($request);
		}
		return redirect('guest')->with('fails', 'You are not Autherised');
	}
}
