<?php
class PHPUnit_Framework_MockObject_Matcher_AnyParameters extends PHPUnit_Framework_MockObject_Matcher_StatelessInvocation
{
    public function toString()
    {
        return 'with any parameters';
    }
    public function matches(PHPUnit_Framework_MockObject_Invocation $invocation)
    {
        return TRUE;
    }
}
