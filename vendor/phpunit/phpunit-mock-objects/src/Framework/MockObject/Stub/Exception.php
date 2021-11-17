<?php
class PHPUnit_Framework_MockObject_Stub_Exception implements PHPUnit_Framework_MockObject_Stub
{
    protected $exception;
    public function __construct(Exception $exception)
    {
        $this->exception = $exception;
    }
    public function invoke(PHPUnit_Framework_MockObject_Invocation $invocation)
    {
        throw $this->exception;
    }
    public function toString()
    {
        return sprintf(
          'raise user-specified exception %s',
          PHPUnit_Util_Type::export($this->exception)
        );
    }
}
