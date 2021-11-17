<?php namespace Illuminate\Queue;
use ReflectionClass;
use ReflectionProperty;
use Illuminate\Contracts\Queue\QueueableEntity;
use Illuminate\Contracts\Database\ModelIdentifier;
trait SerializesModels {
	public function __sleep()
	{
		$properties = (new ReflectionClass($this))->getProperties();
		foreach ($properties as $property)
		{
			$property->setValue($this, $this->getSerializedPropertyValue(
				$this->getPropertyValue($property)
			));
		}
		return array_map(function($p) { return $p->getName(); }, $properties);
	}
	public function __wakeup()
	{
		foreach ((new ReflectionClass($this))->getProperties() as $property)
		{
			$property->setValue($this, $this->getRestoredPropertyValue(
				$this->getPropertyValue($property)
			));
		}
	}
	protected function getSerializedPropertyValue($value)
	{
		return $value instanceof QueueableEntity
						? new ModelIdentifier(get_class($value), $value->getQueueableId()) : $value;
	}
	protected function getRestoredPropertyValue($value)
	{
		return $value instanceof ModelIdentifier
						? (new $value->class)->findOrFail($value->id) : $value;
	}
	protected function getPropertyValue(ReflectionProperty $property)
	{
		$property->setAccessible(true);
		return $property->getValue($this);
	}
}
