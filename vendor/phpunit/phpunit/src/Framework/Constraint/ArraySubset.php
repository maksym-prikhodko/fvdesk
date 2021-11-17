<?php
class PHPUnit_Framework_Constraint_ArraySubset extends PHPUnit_Framework_Constraint
{
    protected $subset;
    protected $strict;
    public function __construct($subset, $strict = false)
    {
        parent::__construct();
        $this->strict  = $strict;
        $this->subset = $subset;
    }
    protected function matches($other)
    {
        $patched = array_replace_recursive($other, $this->subset);
        if ($this->strict) {
            return $other === $patched;
        } else {
            return $other == $patched;
        }
    }
    public function toString()
    {
        return 'has the subset ' . $this->exporter->export($this->subset);
    }
    protected function failureDescription($other)
    {
        return 'an array ' . $this->toString();
    }
}
