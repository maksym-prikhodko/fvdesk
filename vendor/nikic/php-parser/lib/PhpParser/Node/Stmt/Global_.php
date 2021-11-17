<?php
namespace PhpParser\Node\Stmt;
use PhpParser\Node;
class Global_ extends Node\Stmt
{
    public $vars;
    public function __construct(array $vars, array $attributes = array()) {
        parent::__construct(null, $attributes);
        $this->vars = $vars;
    }
    public function getSubNodeNames() {
        return array('vars');
    }
}
