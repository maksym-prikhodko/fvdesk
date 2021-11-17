<?php
class PHPUnit_Framework_Constraint_ArrayHasKey extends PHPUnit_Framework_Constraint
{
    protected $key;
    public function __construct($key)
    {
        parent::__construct();
        $this->key = $key;
    }
    protected function matches($other)
    {
        if (is_array($other)) {
            return array_key_exists($this->key, $other);
        }
        if ($other instanceof ArrayAccess) {
            return $other->offsetExists($this->key);
        }
        return false;
    }
    public function toString()
    {
        return 'has the key ' . $this->exporter->export($this->key);
    }
    protected function failureDescription($other)
    {
        return 'an array ' . $this->toString();
    }
}