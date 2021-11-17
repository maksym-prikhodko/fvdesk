<?php
class PHPUnit_Framework_MockObject_Stub_ReturnSelf implements PHPUnit_Framework_MockObject_Stub
{
    public function invoke(PHPUnit_Framework_MockObject_Invocation $invocation)
    {
        if (!$invocation instanceof PHPUnit_Framework_MockObject_Invocation_Object) {
            throw new PHPUnit_Framework_Exception(
                'The current object can only be returned when mocking an ' .
                'object, not a static class.'
            );
        }
        return $invocation->object;
    }
    public function toString()
    {
        return 'return the current object';
    }
}
