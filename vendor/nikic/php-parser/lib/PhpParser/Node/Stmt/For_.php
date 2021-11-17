<?php
namespace PhpParser\Node\Stmt;
use PhpParser\Node;
class For_ extends Node\Stmt
{
    public $init;
    public $cond;
    public $loop;
    public $stmts;
    public function __construct(array $subNodes = array(), array $attributes = array()) {
        parent::__construct(null, $attributes);
        $this->init = isset($subNodes['init']) ? $subNodes['init'] : array();
        $this->cond = isset($subNodes['cond']) ? $subNodes['cond'] : array();
        $this->loop = isset($subNodes['loop']) ? $subNodes['loop'] : array();
        $this->stmts = isset($subNodes['stmts']) ? $subNodes['stmts'] : array();
    }
    public function getSubNodeNames() {
        return array('init', 'cond', 'loop', 'stmts');
    }
}
