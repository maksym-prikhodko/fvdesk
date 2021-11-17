<?php
namespace PhpParser;
abstract class NodeAbstract implements Node
{
    private $subNodeNames;
    protected $attributes;
    public function __construct($subNodes = array(), array $attributes = array()) {
        $this->attributes = $attributes;
        if (null !== $subNodes) {
            foreach ($subNodes as $name => $value) {
                $this->$name = $value;
            }
            $this->subNodeNames = array_keys($subNodes);
        }
    }
    public function getType() {
        return strtr(substr(rtrim(get_class($this), '_'), 15), '\\', '_');
    }
    public function getSubNodeNames() {
        return $this->subNodeNames;
    }
    public function getLine() {
        return $this->getAttribute('startLine', -1);
    }
    public function setLine($line) {
        $this->setAttribute('startLine', (int) $line);
    }
    public function getDocComment() {
        $comments = $this->getAttribute('comments');
        if (!$comments) {
            return null;
        }
        $lastComment = $comments[count($comments) - 1];
        if (!$lastComment instanceof Comment\Doc) {
            return null;
        }
        return $lastComment;
    }
    public function setAttribute($key, $value) {
        $this->attributes[$key] = $value;
    }
    public function hasAttribute($key) {
        return array_key_exists($key, $this->attributes);
    }
    public function &getAttribute($key, $default = null) {
        if (!array_key_exists($key, $this->attributes)) {
            return $default;
        } else {
            return $this->attributes[$key];
        }
    }
    public function getAttributes() {
        return $this->attributes;
    }
}
