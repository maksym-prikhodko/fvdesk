<?php
class PHPUnit_Framework_MockObject_Matcher_InvokedAtLeastOnce extends PHPUnit_Framework_MockObject_Matcher_InvokedRecorder
{
    public function toString()
    {
        return 'invoked at least once';
    }
    public function verify()
    {
        $count = $this->getInvocationCount();
        if ($count < 1) {
            throw new PHPUnit_Framework_ExpectationFailedException(
              'Expected invocation at least once but it never occured.'
            );
        }
    }
}
