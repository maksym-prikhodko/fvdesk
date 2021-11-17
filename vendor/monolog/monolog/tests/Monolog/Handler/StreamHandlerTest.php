<?php
namespace Monolog\Handler;
use Monolog\TestCase;
use Monolog\Logger;
class StreamHandlerTest extends TestCase
{
    public function testWrite()
    {
        $handle = fopen('php:
        $handler = new StreamHandler($handle);
        $handler->setFormatter($this->getIdentityFormatter());
        $handler->handle($this->getRecord(Logger::WARNING, 'test'));
        $handler->handle($this->getRecord(Logger::WARNING, 'test2'));
        $handler->handle($this->getRecord(Logger::WARNING, 'test3'));
        fseek($handle, 0);
        $this->assertEquals('testtest2test3', fread($handle, 100));
    }
    public function testClose()
    {
        $handle = fopen('php:
        $handler = new StreamHandler($handle);
        $this->assertTrue(is_resource($handle));
        $handler->close();
        $this->assertFalse(is_resource($handle));
    }
    public function testWriteCreatesTheStreamResource()
    {
        $handler = new StreamHandler('php:
        $handler->handle($this->getRecord());
    }
    public function testWriteLocking()
    {
        $temp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'monolog_locked_log';
        $handler = new StreamHandler($temp, Logger::DEBUG, true, null, true);
        $handler->handle($this->getRecord());
    }
    public function testWriteMissingResource()
    {
        $handler = new StreamHandler(null);
        $handler->handle($this->getRecord());
    }
    public function invalidArgumentProvider()
    {
        return array(
            array(1),
            array(array()),
            array(array('bogus:
        );
    }
    public function testWriteInvalidArgument($invalidArgument)
    {
        $handler = new StreamHandler($invalidArgument);
    }
    public function testWriteInvalidResource()
    {
        $handler = new StreamHandler('bogus:
        $handler->handle($this->getRecord());
    }
    public function testWriteNonExistingResource()
    {
        $handler = new StreamHandler('/foo/bar/baz/'.rand(0, 10000));
        $handler->handle($this->getRecord());
    }
}
