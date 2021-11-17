<?php
class PHPUnit_Framework_Constraint_ExceptionCode extends PHPUnit_Framework_Constraint
{
    protected $expectedCode;
    public function __construct($expected)
    {
        parent::__construct();
        $this->expectedCode = $expected;
    }
    protected function matches($other)
    {
        return (string) $other->getCode() == (string) $this->expectedCode;
    }
    protected function failureDescription($other)
    {
        return sprintf(
            '%s is equal to expected exception code %s',
            $this->exporter->export($other->getCode()),
            $this->exporter->export($this->expectedCode)
        );
    }
    public function toString()
    {
        return 'exception code is ';
    }
}
