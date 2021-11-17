<?php
class PHPUnit_Framework_Constraint_ClassHasStaticAttribute extends PHPUnit_Framework_Constraint_ClassHasAttribute
{
    protected function matches($other)
    {
        $class = new ReflectionClass($other);
        if ($class->hasProperty($this->attributeName)) {
            $attribute = $class->getProperty($this->attributeName);
            return $attribute->isStatic();
        } else {
            return false;
        }
    }
    public function toString()
    {
        return sprintf(
            'has static attribute "%s"',
            $this->attributeName
        );
    }
}
