<?php
namespace PhpParser\Node\Expr;
use PhpParser\Node\Expr;
class PropertyFetch extends Expr
{
    public $var;
    public $name;
    public function __construct(Expr $var, $name, array $attributes = array()) {
        parent::__construct(null, $attributes);
        $this->var = $var;
        $this->name = $name;
    }
    public function getSubNodeNames() {
        return array('var', 'name');
    }
}
