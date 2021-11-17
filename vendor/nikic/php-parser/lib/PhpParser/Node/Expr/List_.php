<?php
namespace PhpParser\Node\Expr;
use PhpParser\Node\Expr;
class List_ extends Expr
{
    public $vars;
    public function __construct(array $vars, array $attributes = array()) {
        parent::__construct(null, $attributes);
        $this->vars = $vars;
    }
    public function getSubNodeNames() {
        return array('vars');
    }
}
