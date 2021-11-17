<?php
namespace PhpParser\Node\Name;
class Relative extends \PhpParser\Node\Name
{
    public function isUnqualified() {
        return false;
    }
    public function isQualified() {
        return false;
    }
    public function isFullyQualified() {
        return false;
    }
    public function isRelative() {
        return true;
    }
}
