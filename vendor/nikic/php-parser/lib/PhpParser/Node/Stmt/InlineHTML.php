<?php
namespace PhpParser\Node\Stmt;
use PhpParser\Node\Stmt;
class InlineHTML extends Stmt
{
    public $value;
    public function __construct($value, array $attributes = array()) {
        parent::__construct(null, $attributes);
        $this->value = $value;
    }
    public function getSubNodeNames() {
        return array('value');
    }
}
