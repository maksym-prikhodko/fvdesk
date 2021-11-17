<?php namespace Illuminate\Contracts\Routing;
interface TerminableMiddleware extends Middleware {
	public function terminate($request, $response);
}
