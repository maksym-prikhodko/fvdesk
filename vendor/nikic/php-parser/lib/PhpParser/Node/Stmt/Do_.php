<?php
namespace PhpParser\Node\Stmt;
use PhpParser\Node;
class Do_ extends Node\Stmt
{
    public $cond;
    public $stmts;
    public function __construct(Node\Expr $cond, array $stmts = array(), array $attributes = array()) {
        parent::__construct(null, $attributes);
        $this->cond = $cond;
        $this->stmts = $stmts;
    }
    public function getSubNodeNames() {
        return array('cond', 'stmts');
    }
}
