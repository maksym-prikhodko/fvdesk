<?php
namespace Symfony\Component\HttpFoundation\Tests\Session\Storage\Handler;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\NativeSessionHandler;
class NativeSessionHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $handler = new NativeSessionHandler();
        if (PHP_VERSION_ID < 50400) {
            $this->assertFalse($handler instanceof \SessionHandler);
            $this->assertTrue($handler instanceof NativeSessionHandler);
        } else {
            $this->assertTrue($handler instanceof \SessionHandler);
            $this->assertTrue($handler instanceof NativeSessionHandler);
        }
    }
}
