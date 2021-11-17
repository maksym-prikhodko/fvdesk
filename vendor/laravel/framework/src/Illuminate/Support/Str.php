<?php namespace Illuminate\Support;
use RuntimeException;
use Stringy\StaticStringy;
use Illuminate\Support\Traits\Macroable;
class Str {
	use Macroable;
	protected static $snakeCache = [];
	protected static $camelCache = [];
	protected static $studlyCache = [];
	public static function ascii($value)
	{
		return StaticStringy::toAscii($value);
	}
	public static function camel($value)
	{
		if (isset(static::$camelCache[$value]))
		{
			return static::$camelCache[$value];
		}
		return static::$camelCache[$value] = lcfirst(static::studly($value));
	}
	public static function contains($haystack, $needles)
	{
		foreach ((array) $needles as $needle)
		{
			if ($needle != '' && strpos($haystack, $needle) !== false) return true;
		}
		return false;
	}
	public static function endsWith($haystack, $needles)
	{
		foreach ((array) $needles as $needle)
		{
			if ((string) $needle === substr($haystack, -strlen($needle))) return true;
		}
		return false;
	}
	public static function finish($value, $cap)
	{
		$quoted = preg_quote($cap, '/');
		return preg_replace('/(?:'.$quoted.')+$/', '', $value).$cap;
	}
	public static function is($pattern, $value)
	{
		if ($pattern == $value) return true;
		$pattern = preg_quote($pattern, '#');
		$pattern = str_replace('\*', '.*', $pattern).'\z';
		return (bool) preg_match('#^'.$pattern.'#', $value);
	}
	public static function length($value)
	{
		return mb_strlen($value);
	}
	public static function limit($value, $limit = 100, $end = '...')
	{
		if (mb_strlen($value) <= $limit) return $value;
		return rtrim(mb_substr($value, 0, $limit, 'UTF-8')).$end;
	}
	public static function lower($value)
	{
		return mb_strtolower($value);
	}
	public static function words($value, $words = 100, $end = '...')
	{
		preg_match('/^\s*+(?:\S++\s*+){1,'.$words.'}/u', $value, $matches);
		if ( ! isset($matches[0]) || strlen($value) === strlen($matches[0])) return $value;
		return rtrim($matches[0]).$end;
	}
	public static function parseCallback($callback, $default)
	{
		return static::contains($callback, '@') ? explode('@', $callback, 2) : array($callback, $default);
	}
	public static function plural($value, $count = 2)
	{
		return Pluralizer::plural($value, $count);
	}
	public static function random($length = 16)
	{
		if ( ! function_exists('openssl_random_pseudo_bytes'))
		{
			throw new RuntimeException('OpenSSL extension is required.');
		}
		$bytes = openssl_random_pseudo_bytes($length * 2);
		if ($bytes === false)
		{
			throw new RuntimeException('Unable to generate random string.');
		}
		return substr(str_replace(array('/', '+', '='), '', base64_encode($bytes)), 0, $length);
	}
	public static function quickRandom($length = 16)
	{
		$pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		return substr(str_shuffle(str_repeat($pool, $length)), 0, $length);
	}
	public static function upper($value)
	{
		return mb_strtoupper($value);
	}
	public static function title($value)
	{
		return mb_convert_case($value, MB_CASE_TITLE, 'UTF-8');
	}
	public static function singular($value)
	{
		return Pluralizer::singular($value);
	}
	public static function slug($title, $separator = '-')
	{
		$title = static::ascii($title);
		$flip = $separator == '-' ? '_' : '-';
		$title = preg_replace('!['.preg_quote($flip).']+!u', $separator, $title);
		$title = preg_replace('![^'.preg_quote($separator).'\pL\pN\s]+!u', '', mb_strtolower($title));
		$title = preg_replace('!['.preg_quote($separator).'\s]+!u', $separator, $title);
		return trim($title, $separator);
	}
	public static function snake($value, $delimiter = '_')
	{
		$key = $value.$delimiter;
		if (isset(static::$snakeCache[$key]))
		{
			return static::$snakeCache[$key];
		}
		if ( ! ctype_lower($value))
		{
			$value = strtolower(preg_replace('/(.)(?=[A-Z])/', '$1'.$delimiter, $value));
		}
		return static::$snakeCache[$key] = $value;
	}
	public static function startsWith($haystack, $needles)
	{
		foreach ((array) $needles as $needle)
		{
			if ($needle != '' && strpos($haystack, $needle) === 0) return true;
		}
		return false;
	}
	public static function studly($value)
	{
		$key = $value;
		if (isset(static::$studlyCache[$key]))
		{
			return static::$studlyCache[$key];
		}
		$value = ucwords(str_replace(array('-', '_'), ' ', $value));
		return static::$studlyCache[$key] = str_replace(' ', '', $value);
	}
}
