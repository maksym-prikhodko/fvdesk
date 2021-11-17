<?php
namespace PhpParser\Node\Expr;
use PhpParser\Node\Expr;
class ClosureUse extends Expr
{
    public $var;
    public $byRef;
    public function __construct($var, $byRef = false, array $attributes = array()) {
        parent::__construct(null, $attributes);
        $this->var = $var;
        $this->byRef = $byRef;
    }
    public function getSubNodeNames() {
        return array('var', 'byRef');
    }
}
