<?php
class PHPUnit_Framework_MockObject_Invocation_Object extends PHPUnit_Framework_MockObject_Invocation_Static
{
    public $object;
    public function __construct($className, $methodName, array $parameters, $object, $cloneObjects = FALSE)
    {
        parent::__construct($className, $methodName, $parameters, $cloneObjects);
        $this->object = $object;
    }
}
