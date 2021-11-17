<?php namespace SuperClosure\Analyzer;
class Token
{
    public $name;
    public $value;
    public $code;
    public $line;
    public function __construct($code, $value = null, $line = null)
    {
        if (is_array($code)) {
            list($value, $code, $line) = array_pad($code, 3, null);
        }
        $this->code = $code;
        $this->value = $value;
        $this->line = $line;
        $this->name = $value ? token_name($value) : null;
    }
    public function is($value)
    {
        return ($this->code === $value || $this->value === $value);
    }
    public function __toString()
    {
        return $this->code;
    }
}
