<?php
interface PHPUnit_Framework_MockObject_MockObject 
{
    public function expects(PHPUnit_Framework_MockObject_Matcher_Invocation $matcher);
    public function __phpunit_setOriginalObject($originalObject);
    public function __phpunit_getInvocationMocker();
    public function __phpunit_verify();
}
