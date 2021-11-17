<?php
namespace PhpParser\Node\Stmt;
use PhpParser\Node;
class Echo_ extends Node\Stmt
{
    public $exprs;
    public function __construct(array $exprs, array $attributes = array()) {
        parent::__construct(null, $attributes);
        $this->exprs = $exprs;
    }
    public function getSubNodeNames() {
        return array('exprs');
    }
}
