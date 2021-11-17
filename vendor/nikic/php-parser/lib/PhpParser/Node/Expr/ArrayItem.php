<?php
namespace PhpParser\Node\Expr;
use PhpParser\Node\Expr;
class ArrayItem extends Expr
{
    public $key;
    public $value;
    public $byRef;
    public function __construct(Expr $value, Expr $key = null, $byRef = false, array $attributes = array()) {
        parent::__construct(null, $attributes);
        $this->key = $key;
        $this->value = $value;
        $this->byRef = $byRef;
    }
    public function getSubNodeNames() {
        return array('key', 'value', 'byRef');
    }
}
