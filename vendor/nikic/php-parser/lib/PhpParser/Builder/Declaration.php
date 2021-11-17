<?php
namespace PhpParser\Builder;
use PhpParser;
use PhpParser\Node;
use PhpParser\Node\Stmt;
abstract class Declaration extends PhpParser\BuilderAbstract
{
    protected $attributes = array();
    abstract public function addStmt($stmt);
    public function addStmts(array $stmts) {
        foreach ($stmts as $stmt) {
            $this->addStmt($stmt);
        }
        return $this;
    }
    public function setDocComment($docComment) {
        $this->attributes['comments'] = array(
            $this->normalizeDocComment($docComment)
        );
        return $this;
    }
}
