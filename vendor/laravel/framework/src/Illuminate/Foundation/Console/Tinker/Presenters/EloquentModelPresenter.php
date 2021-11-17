<?php namespace Illuminate\Foundation\Console\Tinker\Presenters;
use ReflectionClass;
use ReflectionProperty;
use Psy\Presenter\ObjectPresenter;
use Illuminate\Database\Eloquent\Model;
class EloquentModelPresenter extends ObjectPresenter {
	public function canPresent($value)
	{
		return $value instanceof Model;
	}
	public function getProperties($value, ReflectionClass $class, $propertyFilter)
	{
		$attributes = array_merge($value->getAttributes(), $value->getRelations());
		$visible = $value->getVisible();
		if (count($visible) === 0)
		{
			$visible = array_diff(array_keys($attributes), $value->getHidden());
		}
		if ( ! $this->showHidden($propertyFilter))
		{
			return array_intersect_key($attributes, array_flip($visible));
		}
		$properties = [];
		foreach ($attributes as $key => $value)
		{
			if ( ! in_array($key, $visible))
			{
				$key = sprintf('<protected>%s</protected>', $key);
			}
			$properties[$key] = $value;
		}
		return $properties;
	}
	protected function showHidden($propertyFilter)
	{
		return $propertyFilter & (ReflectionProperty::IS_PROTECTED | ReflectionProperty::IS_PRIVATE);
	}
}
