<?php
interface PHPUnit_Framework_MockObject_Matcher_Invocation extends PHPUnit_Framework_SelfDescribing, PHPUnit_Framework_MockObject_Verifiable
{
    public function invoked(PHPUnit_Framework_MockObject_Invocation $invocation);
    public function matches(PHPUnit_Framework_MockObject_Invocation $invocation);
}
