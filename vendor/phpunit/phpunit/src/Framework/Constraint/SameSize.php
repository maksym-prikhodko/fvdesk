<?php
class PHPUnit_Framework_Constraint_SameSize extends PHPUnit_Framework_Constraint_Count
{
    protected $expectedCount;
    public function __construct($expected)
    {
        parent::__construct($this->getCountOf($expected));
    }
}
