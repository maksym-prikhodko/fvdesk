<?php
namespace PhpParser\Node\Expr;
use PhpParser\Node\Expr;
class Array_ extends Expr
{
    public $items;
    public function __construct(array $items = array(), array $attributes = array()) {
        parent::__construct(null, $attributes);
        $this->items = $items;
    }
    public function getSubNodeNames() {
        return array('items');
    }
}
