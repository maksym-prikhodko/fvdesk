<?php
namespace PhpParser\Node\Expr;
use PhpParser\Node\Expr;
class Ternary extends Expr
{
    public $cond;
    public $if;
    public $else;
    public function __construct(Expr $cond, $if, Expr $else, array $attributes = array()) {
        parent::__construct(null, $attributes);
        $this->cond = $cond;
        $this->if = $if;
        $this->else = $else;
    }
    public function getSubNodeNames() {
        return array('cond', 'if', 'else');
    }
}
