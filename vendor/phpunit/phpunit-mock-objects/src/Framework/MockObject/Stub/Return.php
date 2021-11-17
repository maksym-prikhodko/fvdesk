<?php
class PHPUnit_Framework_MockObject_Stub_Return implements PHPUnit_Framework_MockObject_Stub
{
    protected $value;
    public function __construct($value)
    {
        $this->value = $value;
    }
    public function invoke(PHPUnit_Framework_MockObject_Invocation $invocation)
    {
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
