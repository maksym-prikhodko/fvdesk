<?php
namespace Psy\Test\CodeCleaner;
use Psy\CodeCleaner\InstanceOfPass;
class InstanceOfPassTest extends CodeCleanerTestCase
{
    protected function setUp()
    {
        $this->setPass(new InstanceOfPass());
    }
    public function testProcessInvalidStatement($code)
    {
        $stmts = $this->parse($code);
        $this->traverser->traverse($stmts);
    }
    public function invalidStatements()
    {
        return array(
            array('null instanceof stdClass'),
            array('true instanceof stdClass'),
            array('9 instanceof stdClass'),
            array('1.0 instanceof stdClass'),
            array('"foo" instanceof stdClass'),
            array('__DIR__ instanceof stdClass'),
            array('PHP_SAPI instanceof stdClass'),
            array('1+1 instanceof stdClass'),
            array('true && false instanceof stdClass'),
            array('"a"."b" instanceof stdClass'),
            array('!5 instanceof stdClass'),
        );
    }
    public function testProcessValidStatement($code)
    {
        $stmts = $this->parse($code);
        $this->traverser->traverse($stmts);
    }
    public function validStatements()
    {
        $data = array(
            array('$a instanceof stdClass'),
            array('strtolower("foo") instanceof stdClass'),
            array('array(1) instanceof stdClass'),
            array('(string) "foo" instanceof stdClass'),
            array('(1+1) instanceof stdClass'),
            array('"foo ${foo} $bar" instanceof stdClass'),
            array('DateTime::ISO8601 instanceof stdClass'),
        );
        return $data;
    }
}
