<?php
namespace PhpParser\Node\Stmt;
use PhpParser\Node;
class Switch_ extends Node\Stmt
{
    public $cond;
    public $cases;
    public function __construct(Node\Expr $cond, array $cases, array $attributes = array()) {
        parent::__construct(null, $attributes);
        $this->cond = $cond;
        $this->cases = $cases;
    }
    public function getSubNodeNames() {
        return array('cond', 'cases');
    }
}
