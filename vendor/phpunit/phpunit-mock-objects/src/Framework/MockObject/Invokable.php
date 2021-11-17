<?php
interface PHPUnit_Framework_MockObject_Invokable extends PHPUnit_Framework_MockObject_Verifiable
{
    public function invoke(PHPUnit_Framework_MockObject_Invocation $invocation);
    public function matches(PHPUnit_Framework_MockObject_Invocation $invocation);
}
