<?php
class Framework_TestFailureTest extends PHPUnit_Framework_TestCase
{
    public function testToString()
    {
        $test = new self(__FUNCTION__);
        $exception = new PHPUnit_Framework_Exception('message');
        $failure = new PHPUnit_Framework_TestFailure($test, $exception);
        $this->assertEquals(__METHOD__ . ': message', $failure->toString());
    }
}
