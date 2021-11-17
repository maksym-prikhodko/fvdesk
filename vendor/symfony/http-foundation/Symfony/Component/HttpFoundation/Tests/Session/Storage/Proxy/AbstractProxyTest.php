<?php
namespace Symfony\Component\HttpFoundation\Tests\Session\Storage\Proxy;
use Symfony\Component\HttpFoundation\Session\Storage\Proxy\AbstractProxy;
class ConcreteProxy extends AbstractProxy
{
}
class ConcreteSessionHandlerInterfaceProxy extends AbstractProxy implements \SessionHandlerInterface
{
    public function open($savePath, $sessionName)
    {
    }
    public function close()
    {
    }
    public function read($id)
    {
    }
    public function write($id, $data)
    {
    }
    public function destroy($id)
    {
    }
    public function gc($maxlifetime)
    {
    }
}
class AbstractProxyTest extends \PHPUnit_Framework_TestCase
{
    protected $proxy;
    protected function setUp()
    {
        $this->proxy = new ConcreteProxy();
    }
    protected function tearDown()
    {
        $this->proxy = null;
    }
    public function testGetSaveHandlerName()
    {
        $this->assertNull($this->proxy->getSaveHandlerName());
    }
    public function testIsSessionHandlerInterface()
    {
        $this->assertFalse($this->proxy->isSessionHandlerInterface());
        $sh = new ConcreteSessionHandlerInterfaceProxy();
        $this->assertTrue($sh->isSessionHandlerInterface());
    }
    public function testIsWrapper()
    {
        $this->assertFalse($this->proxy->isWrapper());
    }
    public function testIsActivePhp53()
    {
        if (PHP_VERSION_ID >= 50400) {
            $this->markTestSkipped('Test skipped, for PHP 5.3 only.');
        }
        $this->assertFalse($this->proxy->isActive());
    }
    public function testIsActivePhp54()
    {
        if (PHP_VERSION_ID < 50400) {
            $this->markTestSkipped('Test skipped, for PHP 5.4 only.');
        }
        $this->assertFalse($this->proxy->isActive());
        session_start();
        $this->assertTrue($this->proxy->isActive());
    }
    public function testSetActivePhp53()
    {
        if (PHP_VERSION_ID >= 50400) {
            $this->markTestSkipped('Test skipped, for PHP 5.3 only.');
        }
        $this->proxy->setActive(true);
        $this->assertTrue($this->proxy->isActive());
        $this->proxy->setActive(false);
        $this->assertFalse($this->proxy->isActive());
    }
    public function testSetActivePhp54()
    {
        if (PHP_VERSION_ID < 50400) {
            $this->markTestSkipped('Test skipped, for PHP 5.4 only.');
        }
        $this->proxy->setActive(true);
    }
    public function testName()
    {
        $this->assertEquals(session_name(), $this->proxy->getName());
        $this->proxy->setName('foo');
        $this->assertEquals('foo', $this->proxy->getName());
        $this->assertEquals(session_name(), $this->proxy->getName());
    }
    public function testNameExceptionPhp53()
    {
        if (PHP_VERSION_ID >= 50400) {
            $this->markTestSkipped('Test skipped, for PHP 5.3 only.');
        }
        $this->proxy->setActive(true);
        $this->proxy->setName('foo');
    }
    public function testNameExceptionPhp54()
    {
        if (PHP_VERSION_ID < 50400) {
            $this->markTestSkipped('Test skipped, for PHP 5.4 only.');
        }
        session_start();
        $this->proxy->setName('foo');
    }
    public function testId()
    {
        $this->assertEquals(session_id(), $this->proxy->getId());
        $this->proxy->setId('foo');
        $this->assertEquals('foo', $this->proxy->getId());
        $this->assertEquals(session_id(), $this->proxy->getId());
    }
    public function testIdExceptionPhp53()
    {
        if (PHP_VERSION_ID >= 50400) {
            $this->markTestSkipped('Test skipped, for PHP 5.3 only.');
        }
        $this->proxy->setActive(true);
        $this->proxy->setId('foo');
    }
    public function testIdExceptionPhp54()
    {
        if (PHP_VERSION_ID < 50400) {
            $this->markTestSkipped('Test skipped, for PHP 5.4 only.');
        }
        session_start();
        $this->proxy->setId('foo');
    }
}
