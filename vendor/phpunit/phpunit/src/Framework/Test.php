<?php
interface PHPUnit_Framework_Test extends Countable
{
    public function run(PHPUnit_Framework_TestResult $result = null);
}
