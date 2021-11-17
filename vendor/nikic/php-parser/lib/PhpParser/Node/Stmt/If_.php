<?php
namespace PhpParser\Node\Stmt;
use PhpParser\Node;
class If_ extends Node\Stmt
{
    public $cond;
    public $stmts;
    public $elseifs;
    public $else;
    public function __construct(Node\Expr $cond, array $subNodes = array(), array $attributes = array()) {
        parent::__construct(null, $attributes);
        $this->cond = $cond;
        $this->stmts = isset($subNodes['stmts']) ? $subNodes['stmts'] : array();
        $this->elseifs = isset($subNodes['elseifs']) ? $subNodes['elseifs'] : array();
        $this->else = isset($subNodes['else']) ? $subNodes['else'] : null;
    }
    public function getSubNodeNames() {
        return array('cond', 'stmts', 'elseifs', 'else');
    }
}
