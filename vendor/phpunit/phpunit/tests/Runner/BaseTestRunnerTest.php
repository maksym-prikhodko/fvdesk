<?php
class Runner_BaseTestRunnerTest extends PHPUnit_Framework_TestCase
{
    public function testInvokeNonStaticSuite()
    {
        $runner = new MockRunner;
        $runner->getTest('NonStatic');
    }
}
