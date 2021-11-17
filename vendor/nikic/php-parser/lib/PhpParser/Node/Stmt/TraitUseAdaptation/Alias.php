<?php
namespace PhpParser\Node\Stmt\TraitUseAdaptation;
use PhpParser\Node;
class Alias extends Node\Stmt\TraitUseAdaptation
{
    public $newModifier;
    public $newName;
    public function __construct($trait, $method, $newModifier, $newName, array $attributes = array()) {
        parent::__construct(null, $attributes);
        $this->trait = $trait;
        $this->method = $method;
        $this->newModifier = $newModifier;
        $this->newName = $newName;
    }
    public function getSubNodeNames() {
        return array('trait', 'method', 'newModifier', 'newName');
    }
}
