<?php
namespace PhpParser\Builder;
use PhpParser;
use PhpParser\Node\Stmt;
class Property extends PhpParser\BuilderAbstract
{
    protected $name;
    protected $type = 0;
    protected $default = null;
    protected $attributes = array();
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
    public function setDefault($value) {
        $this->default = $this->normalizeValue($value);
        return $this;
    }
    public function setDocComment($docComment) {
        $this->attributes = array(
            'comments' => array($this->normalizeDocComment($docComment))
        );
        return $this;
    }
    public function getNode() {
        return new Stmt\Property(
            $this->type !== 0 ? $this->type : Stmt\Class_::MODIFIER_PUBLIC,
            array(
                new Stmt\PropertyProperty($this->name, $this->default)
            ),
            $this->attributes
        );
    }
}
