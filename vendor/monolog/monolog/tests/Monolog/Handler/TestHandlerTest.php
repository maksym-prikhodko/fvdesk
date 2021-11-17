<?php
namespace Monolog\Handler;
use Monolog\TestCase;
use Monolog\Logger;
class TestHandlerTest extends TestCase
{
    public function testHandler($method, $level)
    {
        $handler = new TestHandler;
        $record = $this->getRecord($level, 'test'.$method);
        $this->assertFalse($handler->{'has'.$method}($record));
        $this->assertFalse($handler->{'has'.$method.'Records'}());
        $handler->handle($record);
        $this->assertFalse($handler->{'has'.$method}('bar'));
        $this->assertTrue($handler->{'has'.$method}($record));
        $this->assertTrue($handler->{'has'.$method}('test'.$method));
        $this->assertTrue($handler->{'has'.$method.'Records'}());
        $records = $handler->getRecords();
        unset($records[0]['formatted']);
        $this->assertEquals(array($record), $records);
    }
    public function methodProvider()
    {
        return array(
            array('Emergency', Logger::EMERGENCY),
            array('Alert'    , Logger::ALERT),
            array('Critical' , Logger::CRITICAL),
            array('Error'    , Logger::ERROR),
            array('Warning'  , Logger::WARNING),
            array('Info'     , Logger::INFO),
            array('Notice'   , Logger::NOTICE),
            array('Debug'    , Logger::DEBUG),
        );
    }
}
