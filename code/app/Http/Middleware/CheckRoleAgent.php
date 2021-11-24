<?php namespace App\Http\Middleware;
use Closure;
class CheckRoleAgent {
	public function handle($request, Closure $next) {
		if ($request->user()->role == 'agent' || $request->user()->role == 'admin') {
			return $next($request);
		}
		return redirect('guest')->with('fails', 'You are not Autherised');
	}
}
