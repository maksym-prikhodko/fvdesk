<?php
namespace Psy\Test\CodeCleaner;
use PHPParser_NodeTraverser as NodeTraverser;
use Psy\CodeCleaner\AbstractClassPass;
class AbstractClassPassTest extends CodeCleanerTestCase
{
    public function setUp()
    {
        $this->pass      = new AbstractClassPass();
        $this->traverser = new NodeTraverser();
        $this->traverser->addVisitor($this->pass);
    }
    public function testProcessStatementFails($code)
    {
        $stmts = $this->parse($code);
        $this->traverser->traverse($stmts);
    }
    public function invalidStatements()
    {
        return array(
            array('class A { abstract function a(); }'),
            array('abstract class B { abstract function b() {} }'),
            array('abstract class B { abstract function b() { echo "yep"; } }'),
        );
    }
    public function testProcessStatementPasses($code)
    {
        $stmts = $this->parse($code);
        $this->traverser->traverse($stmts);
    }
    public function validStatements()
    {
        return array(
            array('abstract class C { function c() {} }'),
            array('abstract class D { abstract function d(); }'),
        );
    }
}
