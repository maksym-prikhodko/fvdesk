<?php namespace Illuminate\Contracts\Bus;
use Closure;
use ArrayAccess;
interface Dispatcher {
	public function dispatchFromArray($command, array $array);
	public function dispatchFrom($command, ArrayAccess $source, array $extras = []);
	public function dispatch($command, Closure $afterResolving = null);
	public function dispatchNow($command, Closure $afterResolving = null);
	public function pipeThrough(array $pipes);
}
