<?php
namespace Monolog\Handler;
use Monolog\TestCase;
use Monolog\Logger;
class CouchDBHandlerTest extends TestCase
{
    public function testHandle()
    {
        $record = $this->getRecord(Logger::WARNING, 'test', array('data' => new \stdClass, 'foo' => 34));
        $expected = array(
            'message' => 'test',
            'context' => array('data' => '[object] (stdClass: {})', 'foo' => 34),
            'level' => Logger::WARNING,
            'level_name' => 'WARNING',
            'channel' => 'test',
            'datetime' => $record['datetime']->format('Y-m-d H:i:s'),
            'extra' => array(),
        );
        $handler = new CouchDBHandler();
        try {
            $handler->handle($record);
        } catch (\RuntimeException $e) {
            $this->markTestSkipped('Could not connect to couchdb server on http:
        }
    }
}
