<?php
namespace Monolog\Processor;
use Monolog\TestCase;
class TagProcessorTest extends TestCase
{
    public function testProcessor()
    {
        $tags = array(1, 2, 3);
        $processor = new TagProcessor($tags);
        $record = $processor($this->getRecord());
        $this->assertEquals($tags, $record['extra']['tags']);
    }
}
