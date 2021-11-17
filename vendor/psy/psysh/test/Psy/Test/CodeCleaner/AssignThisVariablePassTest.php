<?php
namespace Psy\Test\CodeCleaner;
use PHPParser_NodeTraverser as NodeTraverser;
use Psy\CodeCleaner\AssignThisVariablePass;
class AssignThisVariablePassTest extends CodeCleanerTestCase
{
    public function setUp()
    {
        $this->pass      = new AssignThisVariablePass();
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
            array('$this = 3'),
            array('strtolower($this = "this")'),
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
            array('$this'),
            array('$a = $this'),
            array('$a = "this"; $$a = 3'),
            array('$$this = "b"'),
        );
    }
}
