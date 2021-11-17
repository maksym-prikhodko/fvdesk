<?php namespace Illuminate\Support\Traits;
use Closure;
use BadMethodCallException;
trait Macroable {
	protected static $macros = array();
	public static function macro($name, callable $macro)
	{
		static::$macros[$name] = $macro;
	}
	public static function hasMacro($name)
	{
		return isset(static::$macros[$name]);
	}
	public static function __callStatic($method, $parameters)
	{
		if (static::hasMacro($method))
		{
			if (static::$macros[$method] instanceof Closure)
			{
				return call_user_func_array(Closure::bind(static::$macros[$method], null, get_called_class()), $parameters);
			}
			else
			{
				return call_user_func_array(static::$macros[$method], $parameters);
			}
		}
		throw new BadMethodCallException("Method {$method} does not exist.");
	}
	public function __call($method, $parameters)
	{
		if (static::hasMacro($method))
		{
			if (static::$macros[$method] instanceof Closure)
			{
				return call_user_func_array(static::$macros[$method]->bindTo($this, get_class($this)), $parameters);
			}
			else
			{
				return call_user_func_array(static::$macros[$method], $parameters);
			}
		}
		throw new BadMethodCallException("Method {$method} does not exist.");
	}
}
