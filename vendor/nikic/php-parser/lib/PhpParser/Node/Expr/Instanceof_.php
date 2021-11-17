<?php
namespace PhpParser\Node\Expr;
use PhpParser\Node\Name;
use PhpParser\Node\Expr;
class Instanceof_ extends Expr
{
    public $expr;
    public $class;
    public function __construct(Expr $expr, $class, array $attributes = array()) {
        parent::__construct(null, $attributes);
        $this->expr = $expr;
        $this->class = $class;
    }
    public function getSubNodeNames() {
        return array('expr', 'class');
    }
}
