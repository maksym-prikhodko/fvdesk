<?php namespace Illuminate\Support;
use Doctrine\Common\Inflector\Inflector;
class Pluralizer {
	public static $uncountable = array(
		'audio',
		'bison',
		'chassis',
		'compensation',
		'coreopsis',
		'data',
		'deer',
		'education',
		'equipment',
		'fish',
		'gold',
		'information',
		'money',
		'moose',
		'offspring',
		'plankton',
		'police',
		'rice',
		'series',
		'sheep',
		'species',
		'swine',
		'traffic',
	);
	public static function plural($value, $count = 2)
	{
		if ($count === 1 || static::uncountable($value))
		{
			return $value;
		}
		$plural = Inflector::pluralize($value);
		return static::matchCase($plural, $value);
	}
	public static function singular($value)
	{
		$singular = Inflector::singularize($value);
		return static::matchCase($singular, $value);
	}
	protected static function uncountable($value)
	{
		return in_array(strtolower($value), static::$uncountable);
	}
	protected static function matchCase($value, $comparison)
	{
		$functions = array('mb_strtolower', 'mb_strtoupper', 'ucfirst', 'ucwords');
		foreach ($functions as $function)
		{
			if (call_user_func($function, $comparison) === $comparison)
			{
				return call_user_func($function, $value);
			}
		}
		return $value;
	}
}
