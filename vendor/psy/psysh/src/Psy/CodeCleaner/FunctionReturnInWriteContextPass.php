<?php
namespace Psy\CodeCleaner;
use PhpParser\Node;
use PhpParser\Node\Expr\Array_ as ArrayNode;
use PhpParser\Node\Expr\Assign as AssignNode;
use PhpParser\Node\Expr\Empty_ as EmptyNode;
use PhpParser\Node\Expr\FuncCall as FunctionCall;
use PhpParser\Node\Expr\Isset_ as IssetNode;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use Psy\Exception\FatalErrorException;
class FunctionReturnInWriteContextPass extends CodeCleanerPass
{
    const EXCEPTION_MESSAGE = "Can't use function return value in write context";
    private $isPhp55;
    public function __construct()
    {
        $this->isPhp55 = version_compare(PHP_VERSION, '5.5', '>=');
    }
    public function enterNode(Node $node)
    {
        if ($node instanceof ArrayNode || $this->isCallNode($node)) {
            $items = $node instanceof ArrayNode ? $node->items : $node->args;
            foreach ($items as $item) {
                if ($item->byRef && $this->isCallNode($item->value)) {
                    throw new FatalErrorException(self::EXCEPTION_MESSAGE);
                }
            }
        } elseif ($node instanceof IssetNode) {
            foreach ($node->vars as $var) {
                if (!$this->isCallNode($var)) {
                    continue;
                }
                if ($this->isPhp55) {
                    throw new FatalErrorException('Cannot use isset() on the result of a function call (you can use "null !== func()" instead)');
                } else {
                    throw new FatalErrorException(self::EXCEPTION_MESSAGE);
                }
            }
        } elseif ($node instanceof EmptyNode && !$this->isPhp55 && $this->isCallNode($node->expr)) {
            throw new FatalErrorException(self::EXCEPTION_MESSAGE);
        } elseif ($node instanceof AssignNode && $this->isCallNode($node->var)) {
            throw new FatalErrorException(self::EXCEPTION_MESSAGE);
        }
    }
    private function isCallNode(Node $node)
    {
        return $node instanceof FunctionCall || $node instanceof MethodCall || $node instanceof StaticCall;
    }
}
