<?php
class PHPUnit_Framework_MockObject_Builder_InvocationMocker implements PHPUnit_Framework_MockObject_Builder_MethodNameMatch
{
    protected $collection;
    protected $matcher;
    public function __construct(PHPUnit_Framework_MockObject_Stub_MatcherCollection $collection, PHPUnit_Framework_MockObject_Matcher_Invocation $invocationMatcher)
    {
        $this->collection = $collection;
        $this->matcher    = new PHPUnit_Framework_MockObject_Matcher(
          $invocationMatcher
        );
        $this->collection->addMatcher($this->matcher);
    }
    public function getMatcher()
    {
        return $this->matcher;
    }
    public function id($id)
    {
        $this->collection->registerId($id, $this);
        return $this;
    }
    public function will(PHPUnit_Framework_MockObject_Stub $stub)
    {
        $this->matcher->stub = $stub;
        return $this;
    }
    public function willReturn($value)
    {
        $stub = new PHPUnit_Framework_MockObject_Stub_Return(
          $value
        );
        return $this->will($stub);
    }
    public function willReturnMap(array $valueMap)
    {
        $stub = new PHPUnit_Framework_MockObject_Stub_ReturnValueMap(
          $valueMap
        );
        return $this->will($stub);
    }
    public function willReturnArgument($argumentIndex)
    {
        $stub = new PHPUnit_Framework_MockObject_Stub_ReturnArgument(
          $argumentIndex
        );
        return $this->will($stub);
    }
    public function willReturnCallback($callback)
    {
        $stub = new PHPUnit_Framework_MockObject_Stub_ReturnCallback(
          $callback
        );
        return $this->will($stub);
    }
    public function willReturnSelf()
    {
        $stub = new PHPUnit_Framework_MockObject_Stub_ReturnSelf();
        return $this->will($stub);
    }
    public function willReturnOnConsecutiveCalls()
    {
        $args = func_get_args();
        $stub = new PHPUnit_Framework_MockObject_Stub_ConsecutiveCalls($args);
        return $this->will($stub);
    }
    public function willThrowException(Exception $exception)
    {
        $stub = new PHPUnit_Framework_MockObject_Stub_Exception($exception);
        return $this->will($stub);
    }
    public function after($id)
    {
        $this->matcher->afterMatchBuilderId = $id;
        return $this;
    }
    private function canDefineParameters()
    {
        if ($this->matcher->methodNameMatcher === NULL) {
            throw new PHPUnit_Framework_Exception(
              'Method name matcher is not defined, cannot define parameter ' .
              ' matcher without one'
            );
        }
        if ($this->matcher->parametersMatcher !== NULL) {
            throw new PHPUnit_Framework_Exception(
              'Parameter matcher is already defined, cannot redefine'
            );
        }
    }
    public function with()
    {
        $args = func_get_args();
        $this->canDefineParameters();
        $this->matcher->parametersMatcher = new PHPUnit_Framework_MockObject_Matcher_Parameters($args);
        return $this;
    }
    public function withConsecutive() {
        $args = func_get_args();
        $this->canDefineParameters();
        $this->matcher->parametersMatcher =
          new PHPUnit_Framework_MockObject_Matcher_ConsecutiveParameters($args);
        return $this;
    }
    public function withAnyParameters()
    {
        $this->canDefineParameters();
        $this->matcher->parametersMatcher = new PHPUnit_Framework_MockObject_Matcher_AnyParameters;
        return $this;
    }
    public function method($constraint)
    {
        if ($this->matcher->methodNameMatcher !== NULL) {
            throw new PHPUnit_Framework_Exception(
              'Method name matcher is already defined, cannot redefine'
            );
        }
        $this->matcher->methodNameMatcher = new PHPUnit_Framework_MockObject_Matcher_MethodName($constraint);
        return $this;
    }
}
