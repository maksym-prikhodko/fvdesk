<?php namespace Illuminate\Contracts\Container;
use Closure;
interface Container {
	public function bound($abstract);
	public function alias($abstract, $alias);
	public function tag($abstracts, $tags);
	public function tagged($tag);
	public function bind($abstract, $concrete = null, $shared = false);
	public function bindIf($abstract, $concrete = null, $shared = false);
	public function singleton($abstract, $concrete = null);
	public function extend($abstract, Closure $closure);
	public function instance($abstract, $instance);
	public function when($concrete);
	public function make($abstract, $parameters = array());
	public function call($callback, array $parameters = array(), $defaultMethod = null);
	public function resolved($abstract);
	public function resolving($abstract, Closure $callback = null);
	public function afterResolving($abstract, Closure $callback = null);
}
