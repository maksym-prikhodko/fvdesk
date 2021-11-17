<?php
namespace PhpParser\Node\Expr;
use PhpParser\Node\Expr;
class ArrayDimFetch extends Expr
{
    public $var;
    public $dim;
    public function __construct(Expr $var, Expr $dim = null, array $attributes = array()) {
        parent::__construct(null, $attributes);
        $this->var = $var;
        $this->dim = $dim;
    }
    public function getSubnodeNames() {
        return array('var', 'dim');
    }
}
