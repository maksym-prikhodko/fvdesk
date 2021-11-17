<?php
namespace PhpParser\Node\Expr;
use PhpParser\Node\Expr;
abstract class BinaryOp extends Expr
{
    public $left;
    public $right;
    public function __construct(Expr $left, Expr $right, array $attributes = array()) {
        parent::__construct(null, $attributes);
        $this->left = $left;
        $this->right = $right;
    }
    public function getSubNodeNames() {
        return array('left', 'right');
    }
}
