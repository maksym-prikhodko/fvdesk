<?php namespace SuperClosure\Analyzer\Visitor;
use PhpParser\Node as AstNode;
use PhpParser\Node\Expr\Variable as VariableNode;
use PhpParser\NodeVisitorAbstract as NodeVisitor;
final class ThisDetectorVisitor extends NodeVisitor
{
    public $detected = false;
    public function leaveNode(AstNode $node)
    {
        if ($node instanceof VariableNode) {
            if ($node->name === 'this') {
                $this->detected = true;
            }
        }
    }
}
