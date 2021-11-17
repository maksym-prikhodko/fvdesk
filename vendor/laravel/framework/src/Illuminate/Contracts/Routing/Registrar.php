<?php namespace Illuminate\Contracts\Routing;
use Closure;
interface Registrar {
	public function get($uri, $action);
	public function post($uri, $action);
	public function put($uri, $action);
	public function delete($uri, $action);
	public function patch($uri, $action);
	public function options($uri, $action);
	public function match($methods, $uri, $action);
	public function resource($name, $controller, array $options = array());
	public function group(array $attributes, Closure $callback);
	public function before($callback);
	public function after($callback);
	public function filter($name, $callback);
}
