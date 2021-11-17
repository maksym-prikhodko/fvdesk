<?php
namespace PhpParser\Node\Expr;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
class MethodCall extends Expr
{
    public $var;
    public $name;
    public $args;
    public function __construct(Expr $var, $name, array $args = array(), array $attributes = array()) {
        parent::__construct(null, $attributes);
        $this->var = $var;
        $this->name = $name;
        $this->args = $args;
    }
    public function getSubNodeNames() {
        return array('var', 'name', 'args');
    }
}
