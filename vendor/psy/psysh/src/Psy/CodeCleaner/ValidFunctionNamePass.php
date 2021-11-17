<?php
namespace Psy\CodeCleaner;
use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Function_ as FunctionStmt;
use Psy\Exception\FatalErrorException;
class ValidFunctionNamePass extends NamespaceAwarePass
{
    public function leaveNode(Node $node)
    {
        if ($node instanceof FunctionStmt) {
            $name = $this->getFullyQualifiedName($node->name);
            if (function_exists($name) || isset($this->currentScope[strtolower($name)])) {
                throw new FatalErrorException(sprintf('Cannot redeclare %s()', $name), 0, 1, null, $node->getLine());
            }
            $this->currentScope[strtolower($name)] = true;
        } elseif ($node instanceof FuncCall) {
            $name = $node->name;
            if (!$name instanceof Expression && !$name instanceof Variable) {
                $shortName = implode('\\', $name->parts);
                $fullName  = $this->getFullyQualifiedName($name);
                $inScope = isset($this->currentScope[strtolower($fullName)]);
                if (!$inScope && !function_exists($shortName) && !function_exists($fullName)) {
                    $message = sprintf('Call to undefined function %s()', $name);
                    throw new FatalErrorException($message, 0, 1, null, $node->getLine());
                }
            }
        }
    }
}
