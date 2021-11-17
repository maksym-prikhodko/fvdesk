<?php
namespace PhpParser\Node\Expr;
use PhpParser\Node\Expr;
class Variable extends Expr
{
    public $name;
    public function __construct($name, array $attributes = array()) {
        parent::__construct(null, $attributes);
        $this->name = $name;
    }
    public function getSubNodeNames() {
        return array('name');
    }
}
