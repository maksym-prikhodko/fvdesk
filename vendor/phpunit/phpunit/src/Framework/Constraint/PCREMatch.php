<?php
class PHPUnit_Framework_Constraint_PCREMatch extends PHPUnit_Framework_Constraint
{
    protected $pattern;
    public function __construct($pattern)
    {
        parent::__construct();
        $this->pattern = $pattern;
    }
    protected function matches($other)
    {
        return preg_match($this->pattern, $other) > 0;
    }
    public function toString()
    {
        return sprintf(
            'matches PCRE pattern "%s"',
            $this->pattern
        );
    }
}
