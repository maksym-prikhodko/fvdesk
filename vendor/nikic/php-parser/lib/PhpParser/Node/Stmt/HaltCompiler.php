<?php
namespace PhpParser\Node\Stmt;
use PhpParser\Node\Stmt;
class HaltCompiler extends Stmt
{
    public $remaining;
    public function __construct($remaining, array $attributes = array()) {
        parent::__construct(null, $attributes);
        $this->remaining = $remaining;
    }
    public function getSubNodeNames() {
        return array('remaining');
    }
}
