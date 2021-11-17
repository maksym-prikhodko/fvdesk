<?php
namespace PhpParser\Node\Stmt;
use PhpParser\Node;
class Trait_ extends ClassLike
{
    public function __construct($name, array $stmts = array(), array $attributes = array()) {
        parent::__construct(null, $attributes);
        $this->name = $name;
        $this->stmts = $stmts;
    }
    public function getSubNodeNames() {
        return array('name', 'stmts');
    }
}
