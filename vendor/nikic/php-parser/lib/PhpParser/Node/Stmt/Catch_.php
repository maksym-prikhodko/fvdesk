<?php
namespace PhpParser\Node\Stmt;
use PhpParser\Node;
class Catch_ extends Node\Stmt
{
    public $type;
    public $var;
    public $stmts;
    public function __construct(Node\Name $type, $var, array $stmts = array(), array $attributes = array()) {
        parent::__construct(null, $attributes);
        $this->type = $type;
        $this->var = $var;
        $this->stmts = $stmts;
    }
    public function getSubNodeNames() {
        return array('type', 'var', 'stmts');
    }
}
