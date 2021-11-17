<?php
namespace Psy\Test\CodeCleaner;
use Psy\CodeCleaner\StaticConstructorPass;
class StaticConstructorPassTest extends CodeCleanerTestCase
{
    protected function setUp()
    {
        $this->setPass(new StaticConstructorPass());
    }
    public function testProcessInvalidStatement($code)
    {
        $stmts = $this->parse($code);
        $this->traverser->traverse($stmts);
    }
    public function testProcessInvalidStatementCatchedByParser($code)
    {
        $stmts = $this->parse($code);
        $this->traverser->traverse($stmts);
    }
    public function invalidStatements()
    {
        $statements = array(
            array('class A { public static function A() {}}'),
            array('class A { private static function A() {}}'),
        );
        if (version_compare(PHP_VERSION, '5.3.3', '<')) {
            $statements[] = array('namespace B; class A { private static function A() {}}');
        }
        return $statements;
    }
    public function invalidParserStatements()
    {
        $statements = array(
            array('class A { public static function __construct() {}}'),
            array('class A { private static function __construct() {}}'),
            array('class A { private static function __construct() {} public function A() {}}'),
            array('namespace B; class A { private static function __construct() {}}'),
        );
        return $statements;
    }
    public function testProcessValidStatement($code)
    {
        $stmts = $this->parse($code);
        $this->traverser->traverse($stmts);
    }
    public function validStatements()
    {
        $statements = array(
            array('class A { public static function A() {} public function __construct() {}}'),
            array('class A { private function __construct() {} public static function A() {}}'),
        );
        if (version_compare(PHP_VERSION, '5.3.3', '>=')) {
            $statements[] = array('namespace B; class A { private static function A() {}}');
        }
        return $statements;
    }
}
