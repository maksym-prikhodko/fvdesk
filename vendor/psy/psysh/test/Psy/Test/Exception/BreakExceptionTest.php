<?php
namespace Psy\Test\Exception;
use Psy\Exception\BreakException;
use Psy\Exception\Exception;
class BreakExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testInstance()
    {
        $e = new BreakException();
        $this->assertTrue($e instanceof Exception);
        $this->assertTrue($e instanceof BreakException);
    }
    public function testMessage()
    {
        $e = new BreakException('foo');
        $this->assertContains('foo', $e->getMessage());
        $this->assertEquals('foo', $e->getRawMessage());
    }
}
