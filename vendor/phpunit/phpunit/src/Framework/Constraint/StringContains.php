<?php
class PHPUnit_Framework_Constraint_StringContains extends PHPUnit_Framework_Constraint
{
    protected $string;
    protected $ignoreCase;
    public function __construct($string, $ignoreCase = false)
    {
        parent::__construct();
        $this->string     = $string;
        $this->ignoreCase = $ignoreCase;
    }
    protected function matches($other)
    {
        if ($this->ignoreCase) {
            return stripos($other, $this->string) !== false;
        } else {
            return strpos($other, $this->string) !== false;
        }
    }
    public function toString()
    {
        if ($this->ignoreCase) {
            $string = strtolower($this->string);
        } else {
            $string = $this->string;
        }
        return sprintf(
            'contains "%s"',
            $string
        );
    }
}
