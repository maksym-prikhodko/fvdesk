<?php
class PHPUnit_Framework_MockObject_Matcher_AnyInvokedCount extends PHPUnit_Framework_MockObject_Matcher_InvokedRecorder
{
    public function toString()
    {
        return 'invoked zero or more times';
    }
    public function verify()
    {
    }
}
