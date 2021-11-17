<?php
class PHPUnit_Framework_Constraint_IsAnything extends PHPUnit_Framework_Constraint
{
    public function evaluate($other, $description = '', $returnResult = false)
    {
        return $returnResult ? true : null;
    }
    public function toString()
    {
        return 'is anything';
    }
    public function count()
    {
        return 0;
    }
}
