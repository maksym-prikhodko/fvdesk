<?php
namespace PhpParser\Node\Stmt;
use PhpParser\Node\Stmt;
class Goto_ extends Stmt
{
    public $name;
    public function __construct($name, array $attributes = array()) {
        parent::__construct(null, $attributes);
        $this->name = $name;
    }
    public function getSubNodeNames() {
        return array('name');
    }
}
