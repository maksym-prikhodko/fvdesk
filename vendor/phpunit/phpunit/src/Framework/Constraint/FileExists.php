<?php
class PHPUnit_Framework_Constraint_FileExists extends PHPUnit_Framework_Constraint
{
    protected function matches($other)
    {
        return file_exists($other);
    }
    protected function failureDescription($other)
    {
        return sprintf(
            'file "%s" exists',
            $other
        );
    }
    public function toString()
    {
        return 'file exists';
    }
}
