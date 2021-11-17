<?php
namespace PhpParser\Node\Stmt;
use PhpParser\Node;
use PhpParser\Error;
class TryCatch extends Node\Stmt
{
    public $stmts;
    public $catches;
    public $finallyStmts;
    public function __construct(array $stmts, array $catches, array $finallyStmts = null, array $attributes = array()) {
        if (empty($catches) && null === $finallyStmts) {
            throw new Error('Cannot use try without catch or finally');
        }
        parent::__construct(null, $attributes);
        $this->stmts = $stmts;
        $this->catches = $catches;
        $this->finallyStmts = $finallyStmts;
    }
    public function getSubNodeNames() {
        return array('stmts', 'catches', 'finallyStmts');
    }
}
