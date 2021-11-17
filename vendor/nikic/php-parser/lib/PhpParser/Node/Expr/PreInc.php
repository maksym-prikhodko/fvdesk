<?php
namespace PhpParser\Node\Expr;
use PhpParser\Node\Expr;
class PreInc extends Expr
{
    public $var;
    public function __construct(Expr $var, array $attributes = array()) {
        parent::__construct(null, $attributes);
        $this->var = $var;
    }
    public function getSubNodeNames() {
        return array('var');
    }
}
