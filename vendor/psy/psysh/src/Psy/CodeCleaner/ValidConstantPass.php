<?php
namespace Psy\CodeCleaner;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\ConstFetch;
use Psy\Exception\FatalErrorException;
class ValidConstantPass extends NamespaceAwarePass
{
    public function leaveNode(Node $node)
    {
        if ($node instanceof ConstFetch && count($node->name->parts) > 1) {
            $name = $this->getFullyQualifiedName($node->name);
            if (!defined($name)) {
                throw new FatalErrorException(sprintf('Undefined constant %s', $name), 0, 1, null, $node->getLine());
            }
        } elseif ($node instanceof ClassConstFetch) {
            $this->validateClassConstFetchExpression($node);
        }
    }
    protected function validateClassConstFetchExpression(ClassConstFetch $stmt)
    {
        if (!$stmt->class instanceof Expr) {
            $className = $this->getFullyQualifiedName($stmt->class);
            if (class_exists($className)) {
                $constName = sprintf('%s::%s', $className, $stmt->name);
                if (!defined($constName)) {
                    $msg = sprintf('Class constant \'%s\' not found', $constName);
                    throw new FatalErrorException($msg, 0, 1, null, $stmt->getLine());
                }
            }
        }
    }
}
