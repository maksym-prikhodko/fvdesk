<?php
class ExceptionMessageRegExpTest extends PHPUnit_Framework_TestCase
{
    public function testRegexMessage()
    {
        throw new Exception("A polymorphic exception message");
    }
    public function testRegexMessageExtreme()
    {
        throw new Exception("A polymorphic exception message");
    }
    public function testMessageXdebugScreamCompatibility()
    {
        ini_set('xdebug.scream', '1');
        throw new Exception("Screaming preg_match");
    }
    public function testSimultaneousLiteralAndRegExpExceptionMessage()
    {
        throw new Exception("A variadic exception message");
    }
}
