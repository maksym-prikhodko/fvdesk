<?php
namespace Symfony\Component\HttpFoundation\Tests\Session\Storage\Proxy;
use Symfony\Component\HttpFoundation\Session\Storage\Proxy\SessionHandlerProxy;
class SessionHandlerProxyTest extends \PHPUnit_Framework_TestCase
{
    private $mock;
    private $proxy;
    protected function setUp()
    {
        $this->mock = $this->getMock('SessionHandlerInterface');
        $this->proxy = new SessionHandlerProxy($this->mock);
    }
    protected function tearDown()
    {
        $this->mock = null;
        $this->proxy = null;
    }
    public function testOpen()
    {
        $this->mock->expects($this->once())
            ->method('open')
            ->will($this->returnValue(true));
        $this->assertFalse($this->proxy->isActive());
        $this->proxy->open('name', 'id');
        if (PHP_VERSION_ID < 50400) {
            $this->assertTrue($this->proxy->isActive());
        } else {
            $this->assertFalse($this->proxy->isActive());
        }
    }
    public function testOpenFalse()
    {
        $this->mock->expects($this->once())
            ->method('open')
            ->will($this->returnValue(false));
        $this->assertFalse($this->proxy->isActive());
        $this->proxy->open('name', 'id');
        $this->assertFalse($this->proxy->isActive());
    }
    public function testClose()
    {
        $this->mock->expects($this->once())
            ->method('close')
            ->will($this->returnValue(true));
        $this->assertFalse($this->proxy->isActive());
        $this->proxy->close();
        $this->assertFalse($this->proxy->isActive());
    }
    public function testCloseFalse()
    {
        $this->mock->expects($this->once())
            ->method('close')
            ->will($this->returnValue(false));
        $this->assertFalse($this->proxy->isActive());
        $this->proxy->close();
        $this->assertFalse($this->proxy->isActive());
    }
    public function testRead()
    {
        $this->mock->expects($this->once())
            ->method('read');
        $this->proxy->read('id');
    }
    public function testWrite()
    {
        $this->mock->expects($this->once())
            ->method('write');
        $this->proxy->write('id', 'data');
    }
    public function testDestroy()
    {
        $this->mock->expects($this->once())
            ->method('destroy');
        $this->proxy->destroy('id');
    }
    public function testGc()
    {
        $this->mock->expects($this->once())
            ->method('gc');
        $this->proxy->gc(86400);
    }
}
