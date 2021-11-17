<?php
namespace PhpParser\Node\Stmt;
use PhpParser\Node;
use PhpParser\Error;
class Property extends Node\Stmt
{
    public $type;
    public $props;
    public function __construct($type, array $props, array $attributes = array()) {
        if ($type & Class_::MODIFIER_ABSTRACT) {
            throw new Error('Properties cannot be declared abstract');
        }
        if ($type & Class_::MODIFIER_FINAL) {
            throw new Error('Properties cannot be declared final');
        }
        parent::__construct(null, $attributes);
        $this->type = $type;
        $this->props = $props;
    }
    public function getSubNodeNames() {
        return array('type', 'props');
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
    public function isStatic() {
        return (bool) ($this->type & Class_::MODIFIER_STATIC);
    }
}
