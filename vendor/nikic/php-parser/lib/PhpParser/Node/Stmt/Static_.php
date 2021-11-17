<?php
namespace PhpParser\Node\Stmt;
use PhpParser\Node\Stmt;
class Static_ extends Stmt
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
