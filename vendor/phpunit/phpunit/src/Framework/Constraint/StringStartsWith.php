<?php
class PHPUnit_Framework_Constraint_StringStartsWith extends PHPUnit_Framework_Constraint
{
    protected $prefix;
    public function __construct($prefix)
    {
        parent::__construct();
        $this->prefix = $prefix;
    }
    protected function matches($other)
    {
        return strpos($other, $this->prefix) === 0;
    }
    public function toString()
    {
        return 'starts with "' . $this->prefix . '"';
    }
}
