<?php
namespace Psy\CodeCleaner;
use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified as FullyQualifiedName;
use PhpParser\Node\Stmt\Namespace_ as NamespaceStmt;
abstract class NamespaceAwarePass extends CodeCleanerPass
{
    protected $namespace;
    protected $currentScope;
    public function beforeTraverse(array $nodes)
    {
        $this->namespace    = array();
        $this->currentScope = array();
    }
    public function enterNode(Node $node)
    {
        if ($node instanceof NamespaceStmt) {
            $this->namespace = isset($node->name) ? $node->name->parts : array();
        }
    }
    protected function getFullyQualifiedName($name)
    {
        if ($name instanceof FullyQualifiedName) {
            return implode('\\', $name->parts);
        } elseif ($name instanceof Name) {
            $name = $name->parts;
        } elseif (!is_array($name)) {
            $name = array($name);
        }
        return implode('\\', array_merge($this->namespace, $name));
    }
}
