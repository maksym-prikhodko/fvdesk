<?php
namespace PhpParser\Node\Stmt;
use PhpParser\Node;
class Else_ extends Node\Stmt
{
    public $stmts;
    public function __construct(array $stmts = array(), array $attributes = array()) {
        parent::__construct(null, $attributes);
        $this->stmts = $stmts;
    }
    public function getSubNodeNames() {
        return array('stmts');
    }
}
