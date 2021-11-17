<?php
namespace Psy\CodeCleaner;
use PhpParser\Node;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\Instanceof_ as InstanceofStmt;
use PhpParser\Node\Scalar;
use PhpParser\Node\Scalar\Encapsed;
use Psy\Exception\FatalErrorException;
class InstanceOfPass extends CodeCleanerPass
{
    public function enterNode(Node $node)
    {
        if (!$node instanceof InstanceofStmt) {
            return;
        }
        if (($node->expr instanceof Scalar && !$node->expr instanceof Encapsed) || $node->expr instanceof ConstFetch) {
            throw new FatalErrorException('instanceof expects an object instance, constant given');
        }
    }
}
