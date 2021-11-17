<?php
namespace PhpParser\Node\Expr;
use PhpParser\Node\Expr;
abstract class AssignOp extends Expr
{
    public $var;
    public $expr;
    public function __construct(Expr $var, Expr $expr, array $attributes = array()) {
        parent::__construct(null, $attributes);
        $this->var = $var;
        $this->expr = $expr;
    }
    public function getSubNodeNames() {
        return array('var', 'expr');
    }
}
