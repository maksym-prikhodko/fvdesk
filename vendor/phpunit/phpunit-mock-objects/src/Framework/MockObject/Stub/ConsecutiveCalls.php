<?php
class PHPUnit_Framework_MockObject_Stub_ConsecutiveCalls implements PHPUnit_Framework_MockObject_Stub
{
    protected $stack;
    protected $value;
    public function __construct($stack)
    {
        $this->stack = $stack;
    }
    public function invoke(PHPUnit_Framework_MockObject_Invocation $invocation)
    {
        $this->value = array_shift($this->stack);
        if ($this->value instanceof PHPUnit_Framework_MockObject_Stub) {
            $this->value = $this->value->invoke($invocation);
        }
        return $this->value;
    }
    public function toString()
    {
        return sprintf(
          'return user-specified value %s',
          PHPUnit_Util_Type::export($this->value)
        );
    }
}
