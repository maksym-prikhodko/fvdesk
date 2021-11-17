<?php namespace Illuminate\Foundation\Console\Tinker\Presenters;
use Exception;
use ReflectionClass;
use Psy\Presenter\ObjectPresenter;
use Illuminate\Foundation\Application;
class IlluminateApplicationPresenter extends ObjectPresenter {
	protected static $appProperties = [
		'configurationIsCached',
		'environment',
		'environmentFile',
		'isLocal',
		'routesAreCached',
		'runningUnitTests',
		'version',
		'path',
		'basePath',
		'configPath',
		'databasePath',
		'langPath',
		'publicPath',
		'storagePath',
	];
	public function canPresent($value)
	{
		return $value instanceof Application;
	}
	public function getProperties($value, ReflectionClass $class, $propertyFilter)
	{
		$properties = [];
		foreach (self::$appProperties as $property)
		{
			try
			{
				$val = $value->$property();
				if ( ! is_null($val)) $properties[$property] = $val;
			}
			catch (Exception $e)
			{
			}
		}
		return $properties;
	}
}
