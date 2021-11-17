<?php
namespace PhpParser\Node\Expr;
use PhpParser\Node\Expr;
class ShellExec extends Expr
{
    public $parts;
    public function __construct(array $parts, array $attributes = array()) {
        parent::__construct(null, $attributes);
        $this->parts = $parts;
    }
    public function getSubNodeNames() {
        return array('parts');
    }
}
