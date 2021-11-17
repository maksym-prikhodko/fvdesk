<?php
class Framework_BaseTestListenerTest extends PHPUnit_Framework_TestCase
{
    private $result;
    public function testEndEventsAreCounted()
    {
        $this->result = new PHPUnit_Framework_TestResult;
        $listener = new BaseTestListenerSample();
        $this->result->addListener($listener);
        $test = new Success;
        $test->run($this->result);
        $this->assertEquals(1, $listener->endCount);
    }
}
