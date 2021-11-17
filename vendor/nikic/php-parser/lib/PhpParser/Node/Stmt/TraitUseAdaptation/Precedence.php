<?php
namespace PhpParser\Node\Stmt\TraitUseAdaptation;
use PhpParser\Node;
class Precedence extends Node\Stmt\TraitUseAdaptation
{
    public $insteadof;
    public function __construct(Node\Name $trait, $method, array $insteadof, array $attributes = array()) {
        parent::__construct(null, $attributes);
        $this->trait = $trait;
        $this->method = $method;
        $this->insteadof = $insteadof;
    }
    public function getSubNodeNames() {
        return array('trait', 'method', 'insteadof');
    }
}
