<?php namespace Illuminate\Contracts\Bus;
use Closure;
interface HandlerResolver {
	public function resolveHandler($command);
	public function getHandlerClass($command);
	public function getHandlerMethod($command);
	public function maps(array $commands);
	public function mapUsing(Closure $mapper);
}
