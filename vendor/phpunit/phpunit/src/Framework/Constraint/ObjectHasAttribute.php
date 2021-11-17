<?php
class PHPUnit_Framework_Constraint_ObjectHasAttribute extends PHPUnit_Framework_Constraint_ClassHasAttribute
{
    protected function matches($other)
    {
        $object = new ReflectionObject($other);
        return $object->hasProperty($this->attributeName);
    }
}
