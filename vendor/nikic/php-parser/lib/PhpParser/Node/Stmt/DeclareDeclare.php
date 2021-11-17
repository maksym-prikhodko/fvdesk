<?php
namespace PhpParser\Node\Stmt;
use PhpParser\Node;
class DeclareDeclare extends Node\Stmt
{
    public $key;
    public $value;
    public function __construct($key, Node\Expr $value, array $attributes = array()) {
        parent::__construct(null, $attributes);
        $this->key = $key;
        $this->value = $value;
    }
    public function getSubNodeNames() {
        return array('key', 'value');
    }
}
