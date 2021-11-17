<?php
namespace PhpParser\Node\Stmt;
use PhpParser\Node;
class TraitUse extends Node\Stmt
{
    public $traits;
    public $adaptations;
    public function __construct(array $traits, array $adaptations = array(), array $attributes = array()) {
        parent::__construct(null, $attributes);
        $this->traits = $traits;
        $this->adaptations = $adaptations;
    }
    public function getSubNodeNames() {
        return array('traits', 'adaptations');
    }
}
