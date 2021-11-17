<?php namespace Illuminate\Foundation\Bus;
use ArrayAccess;
trait DispatchesCommands {
	protected function dispatch($command)
	{
		return app('Illuminate\Contracts\Bus\Dispatcher')->dispatch($command);
	}
	protected function dispatchFromArray($command, array $array)
	{
		return app('Illuminate\Contracts\Bus\Dispatcher')->dispatchFromArray($command, $array);
	}
	protected function dispatchFrom($command, ArrayAccess $source, $extras = [])
	{
		return app('Illuminate\Contracts\Bus\Dispatcher')->dispatchFrom($command, $source, $extras);
	}
}
