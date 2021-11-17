<?php
class Extensions_RepeatedTestTest extends PHPUnit_Framework_TestCase
{
    protected $suite;
    public function __construct()
    {
        $this->suite = new PHPUnit_Framework_TestSuite;
        $this->suite->addTest(new Success);
        $this->suite->addTest(new Success);
    }
    public function testRepeatedOnce()
    {
        $test = new PHPUnit_Extensions_RepeatedTest($this->suite, 1);
        $this->assertEquals(2, count($test));
        $result = $test->run();
        $this->assertEquals(2, count($result));
    }
    public function testRepeatedMoreThanOnce()
    {
        $test = new PHPUnit_Extensions_RepeatedTest($this->suite, 3);
        $this->assertEquals(6, count($test));
        $result = $test->run();
        $this->assertEquals(6, count($result));
    }
    public function testRepeatedZero()
    {
        $test = new PHPUnit_Extensions_RepeatedTest($this->suite, 0);
        $this->assertEquals(0, count($test));
        $result = $test->run();
        $this->assertEquals(0, count($result));
    }
    public function testRepeatedNegative()
    {
        try {
            $test = new PHPUnit_Extensions_RepeatedTest($this->suite, -1);
        } catch (Exception $e) {
            return;
        }
        $this->fail('Should throw an Exception');
    }
}
