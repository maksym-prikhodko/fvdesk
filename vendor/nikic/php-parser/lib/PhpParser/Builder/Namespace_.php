<?php
namespace PhpParser\Builder;
use PhpParser;
use PhpParser\Node;
use PhpParser\Node\Stmt;
class Namespace_ extends PhpParser\BuilderAbstract
{
    private $name;
    private $stmts = array();
    public function __construct($name) {
        $this->name = null !== $name ? $this->normalizeName($name) : null;
    }
    public function addStmt($stmt) {
        $this->stmts[] = $this->normalizeNode($stmt);
        return $this;
    }
    public function addStmts(array $stmts) {
        foreach ($stmts as $stmt) {
            $this->addStmt($stmt);
        }
        return $this;
    }
    public function getNode() {
        return new Stmt\Namespace_($this->name, $this->stmts);
    }
}
