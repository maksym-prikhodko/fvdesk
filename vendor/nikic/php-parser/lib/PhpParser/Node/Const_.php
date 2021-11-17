<?php
namespace PhpParser\Node;
use PhpParser\NodeAbstract;
class Const_ extends NodeAbstract
{
    public $name;
    public $value;
    public function __construct($name, Expr $value, array $attributes = array()) {
        parent::__construct(null, $attributes);
        $this->name = $name;
        $this->value = $value;
    }
    public function getSubNodeNames() {
        return array('name', 'value');
    }
}
