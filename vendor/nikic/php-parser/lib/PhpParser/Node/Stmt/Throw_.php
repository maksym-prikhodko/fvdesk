<?php
namespace PhpParser\Node\Stmt;
use PhpParser\Node;
class Throw_ extends Node\Stmt
{
    public $expr;
    public function __construct(Node\Expr $expr, array $attributes = array()) {
        parent::__construct(null, $attributes);
        $this->expr = $expr;
    }
    public function getSubNodeNames() {
        return array('expr');
    }
}
