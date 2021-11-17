<?php
class ExceptionMessageTest extends PHPUnit_Framework_TestCase
{
    public function testLiteralMessage()
    {
        throw new Exception("A literal exception message");
    }
    public function testPatialMessageBegin()
    {
        throw new Exception("A partial exception message");
    }
    public function testPatialMessageMiddle()
    {
        throw new Exception("A partial exception message");
    }
    public function testPatialMessageEnd()
    {
        throw new Exception("A partial exception message");
    }
}
