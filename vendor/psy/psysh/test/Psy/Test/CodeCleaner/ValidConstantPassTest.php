<?php
namespace Psy\Test\CodeCleaner;
use Psy\CodeCleaner\ValidConstantPass;
class ValidConstantPassTest extends CodeCleanerTestCase
{
    public function setUp()
    {
        $this->setPass(new ValidConstantPass());
    }
    public function testProcessInvalidConstantReferences($code)
    {
        $stmts = $this->parse($code);
        $this->traverse($stmts);
    }
    public function getInvalidReferences()
    {
        return array(
            array('Foo\BAR'),
            array('Psy\Test\CodeCleaner\ValidConstantPassTest::FOO'),
            array('DateTime::BACON'),
        );
    }
    public function testProcessValidConstantReferences($code)
    {
        $stmts = $this->parse($code);
        $this->traverse($stmts);
    }
    public function getValidReferences()
    {
        return array(
            array('PHP_EOL'),
            array('NotAClass::FOO'),
            array('DateTime::ATOM'),
            array('$a = new DateTime; $a::ATOM'),
        );
    }
}
