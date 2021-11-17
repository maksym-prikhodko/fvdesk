<?php
namespace PhpParser\Node\Expr;
use PhpParser\Node;
use PhpParser\Node\Expr;
class New_ extends Expr
{
    public $class;
    public $args;
    public function __construct($class, array $args = array(), array $attributes = array()) {
        parent::__construct(null, $attributes);
        $this->class = $class;
        $this->args = $args;
    }
    public function getSubNodeNames() {
        return array('class', 'args');
    }
}
