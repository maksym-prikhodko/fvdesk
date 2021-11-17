<?php namespace Illuminate\Support;
class ClassLoader {
	protected static $directories = array();
	protected static $registered = false;
	public static function load($class)
	{
		$class = static::normalizeClass($class);
		foreach (static::$directories as $directory)
		{
			if (file_exists($path = $directory.DIRECTORY_SEPARATOR.$class))
			{
				require_once $path;
				return true;
			}
		}
		return false;
	}
	public static function normalizeClass($class)
	{
		if ($class[0] == '\\') $class = substr($class, 1);
		return str_replace(array('\\', '_'), DIRECTORY_SEPARATOR, $class).'.php';
	}
	public static function register()
	{
		if ( ! static::$registered)
		{
			static::$registered = spl_autoload_register(array('\Illuminate\Support\ClassLoader', 'load'));
		}
	}
	public static function addDirectories($directories)
	{
		static::$directories = array_unique(array_merge(static::$directories, (array) $directories));
	}
	public static function removeDirectories($directories = null)
	{
		if (is_null($directories))
		{
			static::$directories = array();
		}
		else
		{
			static::$directories = array_diff(static::$directories, (array) $directories);
		}
	}
	public static function getDirectories()
	{
		return static::$directories;
	}
}
