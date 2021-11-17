<?php
namespace PhpParser\Node\Expr;
use PhpParser\Node\Name;
use PhpParser\Node\Expr;
class StaticPropertyFetch extends Expr
{
    public $class;
    public $name;
    public function __construct($class, $name, array $attributes = array()) {
        parent::__construct(null, $attributes);
        $this->class = $class;
        $this->name = $name;
    }
    public function getSubNodeNames() {
        return array('class', 'name');
    }
}
