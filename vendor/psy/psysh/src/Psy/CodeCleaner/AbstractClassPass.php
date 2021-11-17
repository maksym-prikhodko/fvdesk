<?php
namespace Psy\CodeCleaner;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_ as ClassStmt;
use PhpParser\Node\Stmt\ClassMethod;
use Psy\Exception\FatalErrorException;
class AbstractClassPass extends CodeCleanerPass
{
    private $class;
    private $abstractMethods;
    public function enterNode(Node $node)
    {
        if ($node instanceof ClassStmt) {
            $this->class = $node;
            $this->abstractMethods = array();
        } elseif ($node instanceof ClassMethod) {
            if ($node->isAbstract()) {
                $name = sprintf('%s::%s', $this->class->name, $node->name);
                $this->abstractMethods[] = $name;
                if ($node->stmts !== null) {
                    throw new FatalErrorException(sprintf('Abstract function %s cannot contain body', $name));
                }
            }
        }
    }
    public function leaveNode(Node $node)
    {
        if ($node instanceof ClassStmt) {
            $count = count($this->abstractMethods);
            if ($count > 0 && !$node->isAbstract()) {
                throw new FatalErrorException(sprintf(
                    'Class %s contains %d abstract method%s must therefore be declared abstract or implement the remaining methods (%s)',
                    $node->name,
                    $count,
                    ($count === 0) ? '' : 's',
                    implode(', ', $this->abstractMethods)
                ));
            }
        }
    }
}
