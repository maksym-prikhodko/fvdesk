<?php
namespace PhpParser\Node\Name;
class FullyQualified extends \PhpParser\Node\Name
{
    public function isUnqualified() {
        return false;
    }
    public function isQualified() {
        return false;
    }
    public function isFullyQualified() {
        return true;
    }
    public function isRelative() {
        return false;
    }
}
