<?php
namespace PhpParser\Builder;
use PhpParser;
use PhpParser\Node;
use PhpParser\Node\Stmt;
class Method extends FunctionLike
{
    protected $name;
    protected $type = 0;
    protected $stmts = array();
    public function __construct($name) {
        $this->name = $name;
    }
    public function makePublic() {
        $this->setModifier(Stmt\Class_::MODIFIER_PUBLIC);
        return $this;
    }
    public function makeProtected() {
        $this->setModifier(Stmt\Class_::MODIFIER_PROTECTED);
        return $this;
    }
    public function makePrivate() {
        $this->setModifier(Stmt\Class_::MODIFIER_PRIVATE);
        return $this;
    }
    public function makeStatic() {
        $this->setModifier(Stmt\Class_::MODIFIER_STATIC);
        return $this;
    }
    public function makeAbstract() {
        if (!empty($this->stmts)) {
            throw new \LogicException('Cannot make method with statements abstract');
        }
        $this->setModifier(Stmt\Class_::MODIFIER_ABSTRACT);
        $this->stmts = null; 
        return $this;
    }
    public function makeFinal() {
        $this->setModifier(Stmt\Class_::MODIFIER_FINAL);
        return $this;
    }
    public function addStmt($stmt) {
        if (null === $this->stmts) {
            throw new \LogicException('Cannot add statements to an abstract method');
        }
        $this->stmts[] = $this->normalizeNode($stmt);
        return $this;
    }
    public function getNode() {
        return new Stmt\ClassMethod($this->name, array(
            'type'   => $this->type,
            'byRef'  => $this->returnByRef,
            'params' => $this->params,
            'stmts'  => $this->stmts,
        ), $this->attributes);
    }
}
