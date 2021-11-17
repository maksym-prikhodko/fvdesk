<?php
namespace Psy\Test\Exception;
use Psy\Exception\Exception;
use Psy\Exception\ParseErrorException;
class ParseErrorExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testInstance()
    {
        $e = new ParseErrorException();
        $this->assertTrue($e instanceof Exception);
        $this->assertTrue($e instanceof \PHPParser_Error);
        $this->assertTrue($e instanceof ParseErrorException);
    }
    public function testMessage()
    {
        $e = new ParseErrorException('{msg}', 1);
        $this->assertContains('{msg}', $e->getMessage());
        $this->assertContains('PHP Parse error:', $e->getMessage());
    }
    public function testConstructFromParseError()
    {
        $e = ParseErrorException::fromParseError(new \PHPParser_Error('{msg}'));
        $this->assertContains('{msg}', $e->getRawMessage());
        $this->assertContains('PHP Parse error:', $e->getMessage());
    }
}