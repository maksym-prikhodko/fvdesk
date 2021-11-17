<?php
namespace PhpParser\Node\Stmt;
use PhpParser\Node;
class Declare_ extends Node\Stmt
{
    public $declares;
    public $stmts;
    public function __construct(array $declares, array $stmts, array $attributes = array()) {
        parent::__construct(null, $attributes);
        $this->declares = $declares;
        $this->stmts = $stmts;
    }
    public function getSubNodeNames() {
        return array('declares', 'stmts');
    }
}
