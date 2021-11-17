<?php
namespace Psy\Test\CodeCleaner;
use Psy\CodeCleaner\LegacyEmptyPass;
class LegacyEmptyPassTest extends CodeCleanerTestCase
{
    public function setUp()
    {
        $this->setPass(new LegacyEmptyPass());
    }
    public function testProcessInvalidStatement($code)
    {
        $stmts = $this->parse($code);
        $this->traverser->traverse($stmts);
    }
    public function invalidStatements()
    {
        if (version_compare(PHP_VERSION, '5.5', '>=')) {
            return array(
                array('empty()'),
            );
        }
        return array(
            array('empty()'),
            array('empty(null)'),
            array('empty(PHP_EOL)'),
            array('empty("wat")'),
            array('empty(1.1)'),
            array('empty(Foo::$bar)'),
        );
    }
    public function testProcessValidStatement($code)
    {
        $stmts = $this->parse($code);
        $this->traverser->traverse($stmts);
    }
    public function validStatements()
    {
        if (version_compare(PHP_VERSION, '5.5', '<')) {
            return array(
                array('empty($foo)'),
            );
        }
        return array(
            array('empty($foo)'),
            array('empty(null)'),
            array('empty(PHP_EOL)'),
            array('empty("wat")'),
            array('empty(1.1)'),
            array('empty(Foo::$bar)'),
        );
    }
}
