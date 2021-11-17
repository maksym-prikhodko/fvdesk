<?php
class PHPUnit_Framework_MockObject_MockBuilder
{
    private $testCase;
    private $type;
    private $methods = array();
    private $mockClassName = '';
    private $constructorArgs = array();
    private $originalConstructor = TRUE;
    private $originalClone = TRUE;
    private $autoload = TRUE;
    private $cloneArguments = FALSE;
    private $callOriginalMethods = FALSE;
    private $proxyTarget = NULL;
    public function __construct(PHPUnit_Framework_TestCase $testCase, $type)
    {
        $this->testCase = $testCase;
        $this->type     = $type;
    }
    public function getMock()
    {
        return $this->testCase->getMock(
          $this->type,
          $this->methods,
          $this->constructorArgs,
          $this->mockClassName,
          $this->originalConstructor,
          $this->originalClone,
          $this->autoload,
          $this->cloneArguments,
          $this->callOriginalMethods,
          $this->proxyTarget
        );
    }
    public function getMockForAbstractClass()
    {
        return $this->testCase->getMockForAbstractClass(
          $this->type,
          $this->constructorArgs,
          $this->mockClassName,
          $this->originalConstructor,
          $this->originalClone,
          $this->autoload,
          $this->methods,
          $this->cloneArguments
        );
    }
    public function getMockForTrait()
    {
        return $this->testCase->getMockForTrait(
          $this->type,
          $this->constructorArgs,
          $this->mockClassName,
          $this->originalConstructor,
          $this->originalClone,
          $this->autoload,
          $this->methods,
          $this->cloneArguments
        );
    }
    public function setMethods($methods)
    {
        $this->methods = $methods;
        return $this;
    }
    public function setConstructorArgs(array $args)
    {
        $this->constructorArgs = $args;
        return $this;
    }
    public function setMockClassName($name)
    {
        $this->mockClassName = $name;
        return $this;
    }
    public function disableOriginalConstructor()
    {
        $this->originalConstructor = FALSE;
        return $this;
    }
    public function enableOriginalConstructor()
    {
        $this->originalConstructor = TRUE;
        return $this;
    }
    public function disableOriginalClone()
    {
        $this->originalClone = FALSE;
        return $this;
    }
    public function enableOriginalClone()
    {
        $this->originalClone = TRUE;
        return $this;
    }
    public function disableAutoload()
    {
        $this->autoload = FALSE;
        return $this;
    }
    public function enableAutoload()
    {
        $this->autoload = TRUE;
        return $this;
    }
    public function disableArgumentCloning()
    {
        $this->cloneArguments = FALSE;
        return $this;
    }
    public function enableArgumentCloning()
    {
        $this->cloneArguments = TRUE;
        return $this;
    }
    public function enableProxyingToOriginalMethods()
    {
        $this->callOriginalMethods = TRUE;
        return $this;
    }
    public function disableProxyingToOriginalMethods()
    {
        $this->callOriginalMethods = FALSE;
        $this->proxyTarget         = NULL;
        return $this;
    }
    public function setProxyTarget($object)
    {
        $this->proxyTarget = $object;
        return $this;
    }
}
