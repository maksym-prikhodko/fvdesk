<?php
namespace Symfony\Component\HttpFoundation\Tests\Session\Storage\Handler;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\WriteCheckSessionHandler;
class WriteCheckSessionHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function test()
    {
        $wrappedSessionHandlerMock = $this->getMock('SessionHandlerInterface');
        $writeCheckSessionHandler = new WriteCheckSessionHandler($wrappedSessionHandlerMock);
        $wrappedSessionHandlerMock
            ->expects($this->once())
            ->method('close')
            ->with()
            ->will($this->returnValue(true))
        ;
        $this->assertTrue($writeCheckSessionHandler->close());
    }
    public function testWrite()
    {
        $wrappedSessionHandlerMock = $this->getMock('SessionHandlerInterface');
        $writeCheckSessionHandler = new WriteCheckSessionHandler($wrappedSessionHandlerMock);
        $wrappedSessionHandlerMock
            ->expects($this->once())
            ->method('write')
            ->with('foo', 'bar')
            ->will($this->returnValue(true))
        ;
        $this->assertTrue($writeCheckSessionHandler->write('foo', 'bar'));
    }
    public function testSkippedWrite()
    {
        $wrappedSessionHandlerMock = $this->getMock('SessionHandlerInterface');
        $writeCheckSessionHandler = new WriteCheckSessionHandler($wrappedSessionHandlerMock);
        $wrappedSessionHandlerMock
            ->expects($this->once())
            ->method('read')
            ->with('foo')
            ->will($this->returnValue('bar'))
        ;
        $wrappedSessionHandlerMock
            ->expects($this->never())
            ->method('write')
        ;
        $this->assertEquals('bar', $writeCheckSessionHandler->read('foo'));
        $this->assertTrue($writeCheckSessionHandler->write('foo', 'bar'));
    }
    public function testNonSkippedWrite()
    {
        $wrappedSessionHandlerMock = $this->getMock('SessionHandlerInterface');
        $writeCheckSessionHandler = new WriteCheckSessionHandler($wrappedSessionHandlerMock);
        $wrappedSessionHandlerMock
            ->expects($this->once())
            ->method('read')
            ->with('foo')
            ->will($this->returnValue('bar'))
        ;
        $wrappedSessionHandlerMock
            ->expects($this->once())
            ->method('write')
            ->with('foo', 'baZZZ')
            ->will($this->returnValue(true))
        ;
        $this->assertEquals('bar', $writeCheckSessionHandler->read('foo'));
        $this->assertTrue($writeCheckSessionHandler->write('foo', 'baZZZ'));
    }
}
