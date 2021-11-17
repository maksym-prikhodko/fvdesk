<?php
namespace PhpParser\Node\Scalar;
use PhpParser\Node\Scalar;
abstract class MagicConst extends Scalar
{
    public function __construct(array $attributes = array()) {
        parent::__construct(null, $attributes);
    }
    public function getSubNodeNames() {
        return array();
    }
    abstract public function getName();
}
