<?php
class Framework_TestImplementorTest extends PHPUnit_Framework_TestCase
{
    public function testSuccessfulRun()
    {
        $result = new PHPUnit_Framework_TestResult;
        $test = new DoubleTestCase(new Success);
        $test->run($result);
        $this->assertEquals(count($test), count($result));
        $this->assertEquals(0, $result->errorCount());
        $this->assertEquals(0, $result->failureCount());
    }
}
