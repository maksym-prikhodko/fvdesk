<?php
namespace ClassPreloader;
class ClassNode
{
    public $next;
    public $prev;
    public $value;
    public function __construct($value = null, $prev = null)
    {
        $this->value = $value;
        $this->prev = $prev;
    }
}
