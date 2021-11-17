<?php
namespace PhpParser\Builder;
use PhpParser;
use PhpParser\Node;
use PhpParser\Node\Stmt;
class Function_ extends FunctionLike
{
    protected $name;
    protected $stmts = array();
    public function __construct($name) {
        $this->name = $name;
    }
    public function addStmt($stmt) {
        $this->stmts[] = $this->normalizeNode($stmt);
        return $this;
    }
    public function getNode() {
        return new Stmt\Function_($this->name, array(
            'byRef'  => $this->returnByRef,
            'params' => $this->params,
            'stmts'  => $this->stmts,
        ), $this->attributes);
    }
}
