<?php
namespace PhpParser\Builder;
use PhpParser;
use PhpParser\Node;
use PhpParser\Node\Stmt;
abstract class FunctionLike extends Declaration
{
    protected $returnByRef = false;
    protected $params = array();
    public function makeReturnByRef() {
        $this->returnByRef = true;
        return $this;
    }
    public function addParam($param) {
        $param = $this->normalizeNode($param);
        if (!$param instanceof Node\Param) {
            throw new \LogicException(sprintf('Expected parameter node, got "%s"', $param->getType()));
        }
        $this->params[] = $param;
        return $this;
    }
    public function addParams(array $params) {
        foreach ($params as $param) {
            $this->addParam($param);
        }
        return $this;
    }
}
