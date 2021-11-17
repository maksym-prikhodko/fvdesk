<?php
namespace PhpParser\Node\Stmt;
use PhpParser\Node;
class StaticVar extends Node\Stmt
{
    public $name;
    public $default;
    public function __construct($name, Node\Expr $default = null, array $attributes = array()) {
        parent::__construct(null, $attributes);
        $this->name = $name;
        $this->default = $default;
    }
    public function getSubNodeNames() {
        return array('name', 'default');
    }
}
