<?php
namespace PhpParser\Node\Stmt;
use PhpParser\Node;
abstract class TraitUseAdaptation extends Node\Stmt
{
    public $trait;
    public $method;
}
