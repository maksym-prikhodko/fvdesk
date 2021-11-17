<?php
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Support\Debug\Dumper;
if ( ! function_exists('append_config'))
{
	function append_config(array $array)
	{
		$start = 9999;
		foreach ($array as $key => $value)
		{
			if (is_numeric($key))
			{
				$start++;
				$array[$start] = array_pull($array, $key);
			}
		}
		return $array;
	}
}
if ( ! function_exists('array_add'))
{
	function array_add($array, $key, $value)
	{
		return Arr::add($array, $key, $value);
	}
}
if ( ! function_exists('array_build'))
{
	function array_build($array, callable $callback)
	{
		return Arr::build($array, $callback);
	}
}
if ( ! function_exists('array_collapse'))
{
	function array_collapse($array)
	{
		return Arr::collapse($array);
	}
}
if ( ! function_exists('array_divide'))
{
	function array_divide($array)
	{
		return Arr::divide($array);
	}
}
if ( ! function_exists('array_dot'))
{
	function array_dot($array, $prepend = '')
	{
		return Arr::dot($array, $prepend);
	}
}
if ( ! function_exists('array_except'))
{
	function array_except($array, $keys)
	{
		return Arr::except($array, $keys);
	}
}
if ( ! function_exists('array_fetch'))
{
	function array_fetch($array, $key)
	{
		return Arr::fetch($array, $key);
	}
}
if ( ! function_exists('array_first'))
{
	function array_first($array, callable $callback, $default = null)
	{
		return Arr::first($array, $callback, $default);
	}
}
if ( ! function_exists('array_last'))
{
	function array_last($array, $callback, $default = null)
	{
		return Arr::last($array, $callback, $default);
	}
}
if ( ! function_exists('array_flatten'))
{
	function array_flatten($array)
	{
		return Arr::flatten($array);
	}
}
if ( ! function_exists('array_forget'))
{
	function array_forget(&$array, $keys)
	{
		return Arr::forget($array, $keys);
	}
}
if ( ! function_exists('array_get'))
{
	function array_get($array, $key, $default = null)
	{
		return Arr::get($array, $key, $default);
	}
}
if ( ! function_exists('array_has'))
{
	function array_has($array, $key)
	{
		return Arr::has($array, $key);
	}
}
if ( ! function_exists('array_only'))
{
	function array_only($array, $keys)
	{
		return Arr::only($array, $keys);
	}
}
if ( ! function_exists('array_pluck'))
{
	function array_pluck($array, $value, $key = null)
	{
		return Arr::pluck($array, $value, $key);
	}
}
if ( ! function_exists('array_pull'))
{
	function array_pull(&$array, $key, $default = null)
	{
		return Arr::pull($array, $key, $default);
	}
}
if ( ! function_exists('array_set'))
{
	function array_set(&$array, $key, $value)
	{
		return Arr::set($array, $key, $value);
	}
}
if ( ! function_exists('array_sort'))
{
	function array_sort($array, callable $callback)
	{
		return Arr::sort($array, $callback);
	}
}
if ( ! function_exists('array_where'))
{
	function array_where($array, callable $callback)
	{
		return Arr::where($array, $callback);
	}
}
if ( ! function_exists('camel_case'))
{
	function camel_case($value)
	{
		return Str::camel($value);
	}
}
if ( ! function_exists('class_basename'))
{
	function class_basename($class)
	{
		$class = is_object($class) ? get_class($class) : $class;
		return basename(str_replace('\\', '/', $class));
	}
}
if ( ! function_exists('class_uses_recursive'))
{
	function class_uses_recursive($class)
	{
		$results = [];
		foreach (array_merge([$class => $class], class_parents($class)) as $class)
		{
			$results += trait_uses_recursive($class);
		}
		return array_unique($results);
	}
}
if ( ! function_exists('collect'))
{
	function collect($value = null)
	{
		return new Collection($value);
	}
}
if ( ! function_exists('data_get'))
{
	function data_get($target, $key, $default = null)
	{
		if (is_null($key)) return $target;
		foreach (explode('.', $key) as $segment)
		{
			if (is_array($target))
			{
				if ( ! array_key_exists($segment, $target))
				{
					return value($default);
				}
				$target = $target[$segment];
			}
			elseif ($target instanceof ArrayAccess)
			{
				if ( ! isset($target[$segment]))
				{
					return value($default);
				}
				$target = $target[$segment];
			}
			elseif (is_object($target))
			{
				if ( ! isset($target->{$segment}))
				{
					return value($default);
				}
				$target = $target->{$segment};
			}
			else
			{
				return value($default);
			}
		}
		return $target;
	}
}
if ( ! function_exists('dd'))
{
	function dd()
	{
		array_map(function($x) { (new Dumper)->dump($x); }, func_get_args());
		die;
	}
}
if ( ! function_exists('e'))
{
	function e($value)
	{
		return htmlentities($value, ENT_QUOTES, 'UTF-8', false);
	}
}
if ( ! function_exists('ends_with'))
{
	function ends_with($haystack, $needles)
	{
		return Str::endsWith($haystack, $needles);
	}
}
if ( ! function_exists('head'))
{
	function head($array)
	{
		return reset($array);
	}
}
if ( ! function_exists('last'))
{
	function last($array)
	{
		return end($array);
	}
}
if ( ! function_exists('object_get'))
{
	function object_get($object, $key, $default = null)
	{
		if (is_null($key) || trim($key) == '') return $object;
		foreach (explode('.', $key) as $segment)
		{
			if ( ! is_object($object) || ! isset($object->{$segment}))
			{
				return value($default);
			}
			$object = $object->{$segment};
		}
		return $object;
	}
}
if ( ! function_exists('preg_replace_sub'))
{
	function preg_replace_sub($pattern, &$replacements, $subject)
	{
		return preg_replace_callback($pattern, function($match) use (&$replacements)
		{
			foreach ($replacements as $key => $value)
			{
				return array_shift($replacements);
			}
		}, $subject);
	}
}
if ( ! function_exists('snake_case'))
{
	function snake_case($value, $delimiter = '_')
	{
		return Str::snake($value, $delimiter);
	}
}
if ( ! function_exists('starts_with'))
{
	function starts_with($haystack, $needles)
	{
		return Str::startsWith($haystack, $needles);
	}
}
if ( ! function_exists('str_contains'))
{
	function str_contains($haystack, $needles)
	{
		return Str::contains($haystack, $needles);
	}
}
if ( ! function_exists('str_finish'))
{
	function str_finish($value, $cap)
	{
		return Str::finish($value, $cap);
	}
}
if ( ! function_exists('str_is'))
{
	function str_is($pattern, $value)
	{
		return Str::is($pattern, $value);
	}
}
if ( ! function_exists('str_limit'))
{
	function str_limit($value, $limit = 100, $end = '...')
	{
		return Str::limit($value, $limit, $end);
	}
}
if ( ! function_exists('str_plural'))
{
	function str_plural($value, $count = 2)
	{
		return Str::plural($value, $count);
	}
}
if ( ! function_exists('str_random'))
{
	function str_random($length = 16)
	{
		return Str::random($length);
	}
}
if ( ! function_exists('str_replace_array'))
{
	function str_replace_array($search, array $replace, $subject)
	{
		foreach ($replace as $value)
		{
			$subject = preg_replace('/'.$search.'/', $value, $subject, 1);
		}
		return $subject;
	}
}
if ( ! function_exists('str_singular'))
{
	function str_singular($value)
	{
		return Str::singular($value);
	}
}
if ( ! function_exists('str_slug'))
{
	function str_slug($title, $separator = '-')
	{
		return Str::slug($title, $separator);
	}
}
if ( ! function_exists('studly_case'))
{
	function studly_case($value)
	{
		return Str::studly($value);
	}
}
if ( ! function_exists('trait_uses_recursive'))
{
	function trait_uses_recursive($trait)
	{
		$traits = class_uses($trait);
		foreach ($traits as $trait)
		{
			$traits += trait_uses_recursive($trait);
		}
		return $traits;
	}
}
if ( ! function_exists('value'))
{
	function value($value)
	{
		return $value instanceof Closure ? $value() : $value;
	}
}
if ( ! function_exists('with'))
{
	function with($object)
	{
		return $object;
	}
}
