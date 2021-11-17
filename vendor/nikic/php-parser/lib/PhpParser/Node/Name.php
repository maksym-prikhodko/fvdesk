<?php
namespace PhpParser\Node;
use PhpParser\NodeAbstract;
class Name extends NodeAbstract
{
    public $parts;
    public function __construct($parts, array $attributes = array()) {
        if (!is_array($parts)) {
            $parts = explode('\\', $parts);
        }
        parent::__construct(null, $attributes);
        $this->parts = $parts;
    }
    public function getSubNodeNames() {
        return array('parts');
    }
    public function getFirst() {
        return $this->parts[0];
    }
    public function getLast() {
        return $this->parts[count($this->parts) - 1];
    }
    public function isUnqualified() {
        return 1 == count($this->parts);
    }
    public function isQualified() {
        return 1 < count($this->parts);
    }
    public function isFullyQualified() {
        return false;
    }
    public function isRelative() {
        return false;
    }
    public function toString($separator = '\\') {
        return implode($separator, $this->parts);
    }
    public function __toString() {
        return implode('\\', $this->parts);
    }
    public function set($name) {
        $this->parts = $this->prepareName($name);
    }
    public function prepend($name) {
        $this->parts = array_merge($this->prepareName($name), $this->parts);
    }
    public function append($name) {
        $this->parts = array_merge($this->parts, $this->prepareName($name));
    }
    public function setFirst($name) {
        array_splice($this->parts, 0, 1, $this->prepareName($name));
    }
    public function setLast($name) {
        array_splice($this->parts, -1, 1, $this->prepareName($name));
    }
    protected function prepareName($name) {
        if (is_string($name)) {
            return explode('\\', $name);
        } elseif (is_array($name)) {
            return $name;
        } elseif ($name instanceof self) {
            return $name->parts;
        }
        throw new \InvalidArgumentException(
            'When changing a name you need to pass either a string, an array or a Name node'
        );
    }
}
