<?php
namespace Monolog\Processor;
use Monolog\TestCase;
class UidProcessorTest extends TestCase
{
    public function testProcessor()
    {
        $processor = new UidProcessor();
        $record = $processor($this->getRecord());
        $this->assertArrayHasKey('uid', $record['extra']);
    }
}
