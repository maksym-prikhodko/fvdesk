<?php
namespace PhpParser\Node\Stmt;
use PhpParser\Node;
class Function_ extends Node\Stmt
{
    public $byRef;
    public $name;
    public $params;
    public $returnType;
    public $stmts;
    public function __construct($name, array $subNodes = array(), array $attributes = array()) {
        parent::__construct(null, $attributes);
        $this->byRef = isset($subNodes['byRef']) ? $subNodes['byRef'] : false;
        $this->name = $name;
        $this->params = isset($subNodes['params']) ? $subNodes['params'] : array();
        $this->returnType = isset($subNodes['returnType']) ? $subNodes['returnType'] : null;
        $this->stmts = isset($subNodes['stmts']) ? $subNodes['stmts'] : array();
    }
    public function getSubNodeNames() {
        return array('byRef', 'name', 'params', 'returnType', 'stmts');
    }
}
