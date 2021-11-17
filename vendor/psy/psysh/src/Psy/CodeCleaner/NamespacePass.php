<?php
namespace Psy\CodeCleaner;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Namespace_ as NamespaceStmt;
use Psy\CodeCleaner;
class NamespacePass extends CodeCleanerPass
{
    private $namespace = null;
    private $cleaner;
    public function __construct(CodeCleaner $cleaner)
    {
        $this->cleaner = $cleaner;
    }
    public function beforeTraverse(array $nodes)
    {
        $first = reset($nodes);
        if (count($nodes) === 1 && $first instanceof NamespaceStmt && empty($first->stmts)) {
            $this->setNamespace($first->name);
        } else {
            foreach ($nodes as $key => $node) {
                if ($node instanceof NamespaceStmt) {
                    $this->setNamespace(null);
                } elseif ($this->namespace !== null) {
                    $nodes[$key] = new NamespaceStmt($this->namespace, array($node));
                }
            }
        }
        return $nodes;
    }
    private function setNamespace($namespace)
    {
        $this->namespace = $namespace;
        $this->cleaner->setNamespace($namespace === null ? null : $namespace->parts);
    }
}
