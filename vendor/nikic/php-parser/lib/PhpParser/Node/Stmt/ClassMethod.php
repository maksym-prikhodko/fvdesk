<?php
namespace PhpParser\Node\Stmt;
use PhpParser\Node;
use PhpParser\Error;
class ClassMethod extends Node\Stmt
{
    public $type;
    public $byRef;
    public $name;
    public $params;
    public $returnType;
    public $stmts;
    public function __construct($name, array $subNodes = array(), array $attributes = array()) {
        parent::__construct(null, $attributes);
        $this->type = isset($subNodes['type']) ? $subNodes['type'] : 0;
        $this->byRef = isset($subNodes['byRef'])  ? $subNodes['byRef']  : false;
        $this->name = $name;
        $this->params = isset($subNodes['params']) ? $subNodes['params'] : array();
        $this->returnType = isset($subNodes['returnType']) ? $subNodes['returnType'] : null;
        $this->stmts = array_key_exists('stmts', $subNodes) ? $subNodes['stmts'] : array();
        if ($this->type & Class_::MODIFIER_STATIC) {
            switch (strtolower($this->name)) {
                case '__construct':
                    throw new Error(sprintf('Constructor %s() cannot be static', $this->name));
                case '__destruct':
                    throw new Error(sprintf('Destructor %s() cannot be static', $this->name));
                case '__clone':
                    throw new Error(sprintf('Clone method %s() cannot be static', $this->name));
            }
        }
    }
    public function getSubNodeNames() {
        return array('type', 'byRef', 'name', 'params', 'returnType', 'stmts');
    }
    public function isPublic() {
        return ($this->type & Class_::MODIFIER_PUBLIC) !== 0 || $this->type === 0;
    }
    public function isProtected() {
        return (bool) ($this->type & Class_::MODIFIER_PROTECTED);
    }
    public function isPrivate() {
        return (bool) ($this->type & Class_::MODIFIER_PRIVATE);
    }
    public function isAbstract() {
        return (bool) ($this->type & Class_::MODIFIER_ABSTRACT);
    }
    public function isFinal() {
        return (bool) ($this->type & Class_::MODIFIER_FINAL);
    }
    public function isStatic() {
        return (bool) ($this->type & Class_::MODIFIER_STATIC);
    }
}
