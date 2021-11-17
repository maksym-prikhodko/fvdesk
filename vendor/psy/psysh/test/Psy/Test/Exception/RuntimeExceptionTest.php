<?php
namespace Psy\Test\Exception;
use Psy\Exception\Exception;
use Psy\Exception\RuntimeException;
class RuntimeExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testException()
    {
        $msg = 'bananas';
        $e   = new RuntimeException($msg);
        $this->assertTrue($e instanceof Exception);
        $this->assertTrue($e instanceof \RuntimeException);
        $this->assertTrue($e instanceof RuntimeException);
        $this->assertEquals($msg, $e->getMessage());
        $this->assertEquals($msg, $e->getRawMessage());
    }
}
