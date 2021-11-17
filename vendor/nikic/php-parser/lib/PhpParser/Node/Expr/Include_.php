<?php
namespace PhpParser\Node\Expr;
use PhpParser\Node\Expr;
class Include_ extends Expr
{
    const TYPE_INCLUDE      = 1;
    const TYPE_INCLUDE_ONCE = 2;
    const TYPE_REQUIRE      = 3;
    const TYPE_REQUIRE_ONCE = 4;
    public $expr;
    public $type;
    public function __construct(Expr $expr, $type, array $attributes = array()) {
        parent::__construct(null, $attributes);
        $this->expr = $expr;
        $this->type = $type;
    }
    public function getSubNodeNames() {
        return array('expr', 'type');
    }
}
