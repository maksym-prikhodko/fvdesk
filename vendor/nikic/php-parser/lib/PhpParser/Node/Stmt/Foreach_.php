<?php
namespace PhpParser\Node\Stmt;
use PhpParser\Node;
class Foreach_ extends Node\Stmt
{
    public $expr;
    public $keyVar;
    public $byRef;
    public $valueVar;
    public $stmts;
    public function __construct(Node\Expr $expr, Node\Expr $valueVar, array $subNodes = array(), array $attributes = array()) {
        parent::__construct(null, $attributes);
        $this->expr = $expr;
        $this->keyVar = isset($subNodes['keyVar']) ? $subNodes['keyVar'] : null;
        $this->byRef = isset($subNodes['byRef']) ? $subNodes['byRef'] : false;
        $this->valueVar = $valueVar;
        $this->stmts = isset($subNodes['stmts']) ? $subNodes['stmts'] : array();
    }
    public function getSubNodeNames() {
        return array('expr', 'keyVar', 'byRef', 'valueVar', 'stmts');
    }
}
