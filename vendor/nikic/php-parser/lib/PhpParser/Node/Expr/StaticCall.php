<?php
namespace PhpParser\Node\Expr;
use PhpParser\Node;
use PhpParser\Node\Expr;
class StaticCall extends Expr
{
    public $class;
    public $name;
    public $args;
    public function __construct($class, $name, array $args = array(), array $attributes = array()) {
        parent::__construct(null, $attributes);
        $this->class = $class;
        $this->name = $name;
        $this->args = $args;
    }
    public function getSubNodeNames() {
        return array('class', 'name', 'args');
    }
}
