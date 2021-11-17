<?php
namespace PhpParser\Node\Stmt;
use PhpParser\Node;
class ClassConst extends Node\Stmt
{
    public $consts;
    public function __construct(array $consts, array $attributes = array()) {
        parent::__construct(null, $attributes);
        $this->consts = $consts;
    }
    public function getSubNodeNames() {
        return array('consts');
    }
}
