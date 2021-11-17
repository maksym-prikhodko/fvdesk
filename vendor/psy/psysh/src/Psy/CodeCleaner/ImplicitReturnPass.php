<?php
namespace Psy\CodeCleaner;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt\Return_ as ReturnStmt;
class ImplicitReturnPass extends CodeCleanerPass
{
    public function beforeTraverse(array $nodes)
    {
        $last = end($nodes);
        if ($last instanceof Expr) {
            $nodes[count($nodes) - 1] = new ReturnStmt($last, array(
                'startLine' => $last->getLine(),
                'endLine'   => $last->getLine(),
            ));
        }
        return $nodes;
    }
}
