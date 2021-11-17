<?php
namespace Psy\CodeCleaner;
use PhpParser\Node;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_ as ClassStmt;
use PhpParser\Node\Stmt\Trait_ as TraitStmt;
use Psy\Exception\ErrorException;
class CalledClassPass extends CodeCleanerPass
{
    private $inClass;
    public function beforeTraverse(array $nodes)
    {
        $this->inClass = false;
    }
    public function enterNode(Node $node)
    {
        if ($node instanceof ClassStmt || $node instanceof TraitStmt) {
            $this->inClass = true;
        } elseif ($node instanceof FuncCall && !$this->inClass) {
            if (!empty($node->args) && !$this->isNull($node->args[0])) {
                return;
            }
            if (!($node->name instanceof Name)) {
                return;
            }
            $name = strtolower($node->name);
            if (in_array($name, array('get_class', 'get_called_class'))) {
                $msg = sprintf('%s() called without object from outside a class', $name);
                throw new ErrorException($msg, 0, E_USER_WARNING, null, $node->getLine());
            }
        }
    }
    public function leaveNode(Node $node)
    {
        if ($node instanceof ClassStmt) {
            $this->inClass = false;
        }
    }
    private function isNull(Node $node)
    {
        return $node->value instanceof ConstFetch && strtolower($node->value->name) === 'null';
    }
}
