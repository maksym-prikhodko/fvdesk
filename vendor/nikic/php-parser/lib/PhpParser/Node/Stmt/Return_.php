<?php
namespace PhpParser\Node\Stmt;
use PhpParser\Node;
class Return_ extends Node\Stmt
{
    public $expr;
    public function __construct(Node\Expr $expr = null, array $attributes = array()) {
        parent::__construct(null, $attributes);
        $this->expr = $expr;
    }
    public function getSubNodeNames() {
        return array('expr');
    }
}
