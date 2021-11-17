<?php
namespace PhpParser\Node\Expr;
use PhpParser\Node\Name;
use PhpParser\Node\Expr;
class ConstFetch extends Expr
{
    public $name;
    public function __construct(Name $name, array $attributes = array()) {
        parent::__construct(null, $attributes);
        $this->name = $name;
    }
    public function getSubNodeNames() {
        return array('name');
    }
}
