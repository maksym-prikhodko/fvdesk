<?php
class PHPUnit_Framework_Constraint_ClassHasAttribute extends PHPUnit_Framework_Constraint
{
    protected $attributeName;
    public function __construct($attributeName)
    {
        parent::__construct();
        $this->attributeName = $attributeName;
    }
    protected function matches($other)
    {
        $class = new ReflectionClass($other);
        return $class->hasProperty($this->attributeName);
    }
    public function toString()
    {
        return sprintf(
            'has attribute "%s"',
            $this->attributeName
        );
    }
    protected function failureDescription($other)
    {
        return sprintf(
            '%sclass "%s" %s',
            is_object($other) ? 'object of ' : '',
            is_object($other) ? get_class($other) : $other,
            $this->toString()
        );
    }
}
