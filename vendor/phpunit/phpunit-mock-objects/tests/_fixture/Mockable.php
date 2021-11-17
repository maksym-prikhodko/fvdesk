<?php
class Mockable
{
    public $constructorArgs;
    public $cloned;
    public function __construct($arg1 = NULL, $arg2 = NULL)
    {
        $this->constructorArgs = array($arg1, $arg2);
    }
    public function mockableMethod()
    {
        return TRUE;
    }
    public function anotherMockableMethod()
    {
        return TRUE;
    }
    public function __clone()
    {
        $this->cloned = TRUE;
    }
}
