<?php
namespace PhpParser\Node\Expr;
use PhpParser\Node;
use PhpParser\Node\Expr;
class FuncCall extends Expr
{
    public $name;
    public $args;
    public function __construct($name, array $args = array(), array $attributes = array()) {
        parent::__construct(null, $attributes);
        $this->name = $name;
        $this->args = $args;
    }
    public function getSubNodeNames() {
        return array('name', 'args');
    }
}
