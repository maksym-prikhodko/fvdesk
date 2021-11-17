<?php
class PHPUnit_Framework_MockObject_Matcher_InvokedAtMostCount extends PHPUnit_Framework_MockObject_Matcher_InvokedRecorder
{
    private $allowedInvocations;
    public function __construct($allowedInvocations)
    {
        $this->allowedInvocations = $allowedInvocations;
    }
    public function toString()
    {
        return 'invoked at most ' . $this->allowedInvocations . ' times';
    }
    public function verify()
    {
        $count = $this->getInvocationCount();
        if ($count > $this->allowedInvocations) {
            throw new PHPUnit_Framework_ExpectationFailedException(
                'Expected invocation at most ' . $this->allowedInvocations .
                ' times but it occured ' . $count . ' time(s).'
            );
        }
    }
}
