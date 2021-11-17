<?php
namespace Psy\CodeCleaner;
use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall as FunctionCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use Psy\Exception\FatalErrorException;
class CallTimePassByReferencePass extends CodeCleanerPass
{
    public function enterNode(Node $node)
    {
        if (version_compare(PHP_VERSION, '5.4', '<')) {
            return;
        }
        if (!$node instanceof FunctionCall && !$node instanceof MethodCall && !$node instanceof StaticCall) {
            return;
        }
        foreach ($node->args as $arg) {
            if ($arg->byRef) {
                throw new FatalErrorException('Call-time pass-by-reference has been removed');
            }
        }
    }
}
