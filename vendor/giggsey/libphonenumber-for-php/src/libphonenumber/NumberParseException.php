<?php
namespace libphonenumber;
class NumberParseException extends \Exception
{
    const INVALID_COUNTRY_CODE = 0;
    const NOT_A_NUMBER = 1;
    const TOO_SHORT_AFTER_IDD = 2;
    const TOO_SHORT_NSN = 3;
    const TOO_LONG = 4;
    private $errorType;
    public function __construct($errorType, $message, $previous = null)
    {
        parent::__construct($message, $errorType, $previous);
        $this->message = $message;
        $this->errorType = $errorType;
    }
    public function getErrorType()
    {
        return $this->errorType;
    }
    public function __toString()
    {
        return "Error type: " . $this->errorType . ". " . $this->message;
    }
}
