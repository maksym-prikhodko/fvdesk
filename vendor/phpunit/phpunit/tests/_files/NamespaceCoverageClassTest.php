<?php
class NamespaceCoverageClassTest extends PHPUnit_Framework_TestCase
{
    public function testSomething()
    {
        $o = new Foo\CoveredClass;
        $o->publicMethod();
    }
}
