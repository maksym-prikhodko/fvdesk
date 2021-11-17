<?php
namespace PhpParser\Node\Scalar;
use PhpParser\Node\Scalar;
class Encapsed extends Scalar
{
    public $parts;
    public function __construct(array $parts = array(), array $attributes = array()) {
        parent::__construct(null, $attributes);
        $this->parts = $parts;
    }
    public function getSubNodeNames() {
        return array('parts');
    }
}
