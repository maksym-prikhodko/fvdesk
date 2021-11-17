<?php
namespace PhpParser\Node\Expr;
use PhpParser\Node\Expr;
class Exit_ extends Expr
{
    public $expr;
    public function __construct(Expr $expr = null, array $attributes = array()) {
        parent::__construct(null, $attributes);
        $this->expr = $expr;
    }
    public function getSubNodeNames() {
        return array('expr');
    }
}
