<?php
class PHPUnit_Framework_Constraint_JsonMatches extends PHPUnit_Framework_Constraint
{
    protected $value;
    public function __construct($value)
    {
        parent::__construct();
        $this->value = $value;
    }
    protected function matches($other)
    {
        $decodedOther = json_decode($other);
        if (json_last_error()) {
            return false;
        }
        $decodedValue = json_decode($this->value);
        if (json_last_error()) {
            return false;
        }
        return $decodedOther == $decodedValue;
    }
    public function toString()
    {
        return sprintf(
            'matches JSON string "%s"',
            $this->value
        );
    }
}
