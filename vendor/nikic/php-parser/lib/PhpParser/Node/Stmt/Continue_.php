<?php
namespace PhpParser\Node\Stmt;
use PhpParser\Node;
class Continue_ extends Node\Stmt
{
    public $num;
    public function __construct(Node\Expr $num = null, array $attributes = array()) {
        parent::__construct(null, $attributes);
        $this->num = $num;
    }
    public function getSubNodeNames() {
        return array('num');
    }
}
