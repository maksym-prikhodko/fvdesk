<?php
namespace PhpParser\Node\Expr;
use PhpParser\Node;
use PhpParser\Node\Expr;
class Closure extends Expr
{
    public $static;
    public $byRef;
    public $params;
    public $uses;
    public $returnType;
    public $stmts;
    public function __construct(array $subNodes = array(), array $attributes = array()) {
        parent::__construct(null, $attributes);
        $this->static = isset($subNodes['static']) ? $subNodes['static'] : false;
        $this->byRef = isset($subNodes['byRef']) ? $subNodes['byRef'] : false;
        $this->params = isset($subNodes['params']) ? $subNodes['params'] : array();
        $this->uses = isset($subNodes['uses']) ? $subNodes['uses'] : array();
        $this->returnType = isset($subNodes['returnType']) ? $subNodes['returnType'] : null;
        $this->stmts = isset($subNodes['stmts']) ? $subNodes['stmts'] : array();
    }
    public function getSubNodeNames() {
        return array('static', 'byRef', 'params', 'uses', 'returnType', 'stmts');
    }
}
