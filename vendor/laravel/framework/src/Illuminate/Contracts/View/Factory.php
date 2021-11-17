<?php namespace Illuminate\Contracts\View;
interface Factory {
	public function exists($view);
	public function file($path, $data = array(), $mergeData = array());
	public function make($view, $data = array(), $mergeData = array());
	public function share($key, $value = null);
	public function composer($views, $callback, $priority = null);
	public function creator($views, $callback);
	public function addNamespace($namespace, $hints);
}
