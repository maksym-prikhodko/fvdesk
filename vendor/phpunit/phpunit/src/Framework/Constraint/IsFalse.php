<?php
class PHPUnit_Framework_Constraint_IsFalse extends PHPUnit_Framework_Constraint
{
    protected function matches($other)
    {
        return $other === false;
    }
    public function toString()
    {
        return 'is false';
    }
}
