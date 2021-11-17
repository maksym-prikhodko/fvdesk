<?php
namespace Monolog\Formatter;
use Monolog\Logger;
class GelfMessageFormatterTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        if (!class_exists('\Gelf\Message')) {
            $this->markTestSkipped("graylog2/gelf-php or mlehner/gelf-php is not installed");
        }
    }
    public function testDefaultFormatter()
    {
        $formatter = new GelfMessageFormatter();
        $record = array(
            'level' => Logger::ERROR,
            'level_name' => 'ERROR',
            'channel' => 'meh',
            'context' => array(),
            'datetime' => new \DateTime("@0"),
            'extra' => array(),
            'message' => 'log',
        );
        $message = $formatter->format($record);
        $this->assertInstanceOf('Gelf\Message', $message);
        $this->assertEquals(0, $message->getTimestamp());
        $this->assertEquals('log', $message->getShortMessage());
        $this->assertEquals('meh', $message->getFacility());
        $this->assertEquals(null, $message->getLine());
        $this->assertEquals(null, $message->getFile());
        $this->assertEquals($this->isLegacy() ? 3 : 'error', $message->getLevel());
        $this->assertNotEmpty($message->getHost());
        $formatter = new GelfMessageFormatter('mysystem');
        $message = $formatter->format($record);
        $this->assertInstanceOf('Gelf\Message', $message);
        $this->assertEquals('mysystem', $message->getHost());
    }
    public function testFormatWithFileAndLine()
    {
        $formatter = new GelfMessageFormatter();
        $record = array(
            'level' => Logger::ERROR,
            'level_name' => 'ERROR',
            'channel' => 'meh',
            'context' => array('from' => 'logger'),
            'datetime' => new \DateTime("@0"),
            'extra' => array('file' => 'test', 'line' => 14),
            'message' => 'log',
        );
        $message = $formatter->format($record);
        $this->assertInstanceOf('Gelf\Message', $message);
        $this->assertEquals('test', $message->getFile());
        $this->assertEquals(14, $message->getLine());
    }
    public function testFormatInvalidFails()
    {
        $formatter = new GelfMessageFormatter();
        $record = array(
            'level' => Logger::ERROR,
            'level_name' => 'ERROR',
        );
        $formatter->format($record);
    }
    public function testFormatWithContext()
    {
        $formatter = new GelfMessageFormatter();
        $record = array(
            'level' => Logger::ERROR,
            'level_name' => 'ERROR',
            'channel' => 'meh',
            'context' => array('from' => 'logger'),
            'datetime' => new \DateTime("@0"),
            'extra' => array('key' => 'pair'),
            'message' => 'log'
        );
        $message = $formatter->format($record);
        $this->assertInstanceOf('Gelf\Message', $message);
        $message_array = $message->toArray();
        $this->assertArrayHasKey('_ctxt_from', $message_array);
        $this->assertEquals('logger', $message_array['_ctxt_from']);
        $formatter = new GelfMessageFormatter(null, null, 'CTX');
        $message = $formatter->format($record);
        $this->assertInstanceOf('Gelf\Message', $message);
        $message_array = $message->toArray();
        $this->assertArrayHasKey('_CTXfrom', $message_array);
        $this->assertEquals('logger', $message_array['_CTXfrom']);
    }
    public function testFormatWithContextContainingException()
    {
        $formatter = new GelfMessageFormatter();
        $record = array(
            'level' => Logger::ERROR,
            'level_name' => 'ERROR',
            'channel' => 'meh',
            'context' => array('from' => 'logger', 'exception' => array(
                'class' => '\Exception',
                'file'  => '/some/file/in/dir.php:56',
                'trace' => array('/some/file/1.php:23', '/some/file/2.php:3')
            )),
            'datetime' => new \DateTime("@0"),
            'extra' => array(),
            'message' => 'log'
        );
        $message = $formatter->format($record);
        $this->assertInstanceOf('Gelf\Message', $message);
        $this->assertEquals("/some/file/in/dir.php", $message->getFile());
        $this->assertEquals("56", $message->getLine());
    }
    public function testFormatWithExtra()
    {
        $formatter = new GelfMessageFormatter();
        $record = array(
            'level' => Logger::ERROR,
            'level_name' => 'ERROR',
            'channel' => 'meh',
            'context' => array('from' => 'logger'),
            'datetime' => new \DateTime("@0"),
            'extra' => array('key' => 'pair'),
            'message' => 'log'
        );
        $message = $formatter->format($record);
        $this->assertInstanceOf('Gelf\Message', $message);
        $message_array = $message->toArray();
        $this->assertArrayHasKey('_key', $message_array);
        $this->assertEquals('pair', $message_array['_key']);
        $formatter = new GelfMessageFormatter(null, 'EXT');
        $message = $formatter->format($record);
        $this->assertInstanceOf('Gelf\Message', $message);
        $message_array = $message->toArray();
        $this->assertArrayHasKey('_EXTkey', $message_array);
        $this->assertEquals('pair', $message_array['_EXTkey']);
    }
    private function isLegacy()
    {
        return interface_exists('\Gelf\IMessagePublisher');
    }
}
