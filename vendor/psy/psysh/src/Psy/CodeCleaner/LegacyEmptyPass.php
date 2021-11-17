<?php
namespace Psy\CodeCleaner;
use PhpParser\Node;
use PhpParser\Node\Expr\Empty_ as ExprEmpty;
use PhpParser\Node\Expr\Variable;
use Psy\Exception\ParseErrorException;
class LegacyEmptyPass extends CodeCleanerPass
{
    public function enterNode(Node $node)
    {
        if (version_compare(PHP_VERSION, '5.5', '>=')) {
            return;
        }
        if (!$node instanceof ExprEmpty) {
            return;
        }
        if (!$node->expr instanceof Variable) {
            $msg = sprintf('syntax error, unexpected %s', $this->getUnexpectedThing($node->expr));
            throw new ParseErrorException($msg, $node->expr->getLine());
        }
    }
    private function getUnexpectedThing(Node $node)
    {
        switch ($node->getType()) {
            case 'Scalar_String':
            case 'Scalar_LNumber':
            case 'Scalar_DNumber':
                return json_encode($node->value);
            case 'Expr_ConstFetch':
                return (string) $node->name;
            default:
                return $node->getType();
        }
    }
}
