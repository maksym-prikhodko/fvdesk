<?php
class PHPUnit_Framework_TestSuite_DataProvider extends PHPUnit_Framework_TestSuite
{
    public function setDependencies(array $dependencies)
    {
        foreach ($this->tests as $test) {
            $test->setDependencies($dependencies);
        }
    }
}
