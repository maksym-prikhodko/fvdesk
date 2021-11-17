<?php
class PHPUnit_Framework_Constraint_IsNull extends PHPUnit_Framework_Constraint
{
    protected function matches($other)
    {
        return $other === null;
    }
    public function toString()
    {
        return 'is null';
    }
}
