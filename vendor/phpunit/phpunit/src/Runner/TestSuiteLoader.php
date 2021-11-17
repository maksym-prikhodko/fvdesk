<?php
interface PHPUnit_Runner_TestSuiteLoader
{
    public function load($suiteClassName, $suiteClassFile = '');
    public function reload(ReflectionClass $aClass);
}
