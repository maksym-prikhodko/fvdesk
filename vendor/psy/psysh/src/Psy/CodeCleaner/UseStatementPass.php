<?php
namespace Psy\CodeCleaner;
use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified as FullyQualifiedName;
use PhpParser\Node\Stmt\Namespace_ as NamespaceStmt;
use PhpParser\Node\Stmt\Use_ as UseStmt;
class UseStatementPass extends NamespaceAwarePass
{
    private $aliases       = array();
    private $lastAliases   = array();
    private $lastNamespace = null;
    public function enterNode(Node $node)
    {
        if ($node instanceof NamespaceStmt) {
            if (strtolower($node->name) === strtolower($this->lastNamespace)) {
                $this->aliases = $this->lastAliases;
            }
        }
    }
    public function leaveNode(Node $node)
    {
        if ($node instanceof UseStmt) {
            foreach ($node->uses as $use) {
                $this->aliases[strtolower($use->alias)] = $use->name;
            }
            return false;
        } elseif ($node instanceof NamespaceStmt) {
            $this->lastNamespace = $node->name;
            $this->lastAliases   = $this->aliases;
            $this->aliases       = array();
        } else {
            foreach ($node as $name => $subNode) {
                if ($subNode instanceof Name) {
                    if ($replacement = $this->findAlias($subNode)) {
                        $node->$name = $replacement;
                    }
                }
            }
            return $node;
        }
    }
    private function findAlias(Name $name)
    {
        $that = strtolower($name);
        foreach ($this->aliases as $alias => $prefix) {
            if ($that === $alias) {
                return new FullyQualifiedName($prefix->toString());
            } elseif (substr($that, 0, strlen($alias) + 1) === $alias . '\\') {
                return new FullyQualifiedName($prefix->toString() . substr($name, strlen($alias)));
            }
        }
    }
}
