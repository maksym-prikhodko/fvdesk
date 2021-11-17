<?php
namespace PhpSpec\Exception\Example;
use Exception;
class StopOnFailureException extends ExampleException
{
    private $result;
    public function __construct($message = "", $code = 0, Exception $previous = null, $result = 0)
    {
        parent::__construct($message, $code, $previous);
        $this->result = $result;
    }
    public function getResult()
    {
        return $this->result;
    }
}
