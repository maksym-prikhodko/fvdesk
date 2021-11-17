<?php
namespace PhpParser\Node;
use PhpParser\NodeAbstract;
class Arg extends NodeAbstract
{
    public $value;
    public $byRef;
    public $unpack;
    public function __construct(Expr $value, $byRef = false, $unpack = false, array $attributes = array()) {
        parent::__construct(null, $attributes);
        $this->value = $value;
        $this->byRef = $byRef;
        $this->unpack = $unpack;
    }
    public function getSubNodeNames() {
        return array('value', 'byRef', 'unpack');
    }
}
