<?php
class PHPUnit_Framework_Constraint_StringEndsWith extends PHPUnit_Framework_Constraint
{
    protected $suffix;
    public function __construct($suffix)
    {
        parent::__construct();
        $this->suffix = $suffix;
    }
    protected function matches($other)
    {
        return substr($other, 0 - strlen($this->suffix)) == $this->suffix;
    }
    public function toString()
    {
        return 'ends with "' . $this->suffix . '"';
    }
}
