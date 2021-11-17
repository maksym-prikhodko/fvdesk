<?php
namespace PhpParser\Builder;
use PhpParser;
use PhpParser\Node;
class Param extends PhpParser\BuilderAbstract
{
    protected $name;
    protected $default = null;
    protected $type = null;
    protected $byRef = false;
    public function __construct($name) {
        $this->name = $name;
    }
    public function setDefault($value) {
        $this->default = $this->normalizeValue($value);
        return $this;
    }
    public function setTypeHint($type) {
        if ($type === 'array' || $type === 'callable') {
            $this->type = $type;
        } else {
            $this->type = $this->normalizeName($type);
        }
        return $this;
    }
    public function makeByRef() {
        $this->byRef = true;
        return $this;
    }
    public function getNode() {
        return new Node\Param(
            $this->name, $this->default, $this->type, $this->byRef
        );
    }
}
