<?php
namespace Psy\CodeCleaner;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_ as ClassStmt;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Namespace_ as NamespaceStmt;
use Psy\Exception\FatalErrorException;
class StaticConstructorPass extends CodeCleanerPass
{
    private $isPHP533;
    private $namespace;
    public function __construct()
    {
        $this->isPHP533 = version_compare(PHP_VERSION, '5.3.3', '>=');
    }
    public function beforeTraverse(array $nodes)
    {
        $this->namespace = array();
    }
    public function enterNode(Node $node)
    {
        if ($node instanceof NamespaceStmt) {
            $this->namespace = isset($node->name) ? $node->name->parts : array();
        } elseif ($node instanceof ClassStmt) {
            if (!empty($this->namespace) && $this->isPHP533) {
                return;
            }
            $constructor = null;
            foreach ($node->stmts as $stmt) {
                if ($stmt instanceof ClassMethod) {
                    if ('__construct' === strtolower($stmt->name)) {
                        return;
                    }
                    if (strtolower($node->name) === strtolower($stmt->name)) {
                        $constructor = $stmt;
                    }
                }
            }
            if ($constructor && $constructor->isStatic()) {
                throw new FatalErrorException(sprintf(
                    'Constructor %s::%s() cannot be static',
                    implode('\\', array_merge($this->namespace, (array) $node->name)),
                    $constructor->name
                ));
            }
        }
    }
}
