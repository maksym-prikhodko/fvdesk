<?php
namespace PhpParser;
interface Node
{
    public function getType();
    public function getSubNodeNames();
    public function getLine();
    public function setLine($line);
    public function getDocComment();
    public function setAttribute($key, $value);
    public function hasAttribute($key);
    public function &getAttribute($key, $default = null);
    public function getAttributes();
}
