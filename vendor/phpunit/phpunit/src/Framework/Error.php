<?php
class PHPUnit_Framework_Error extends PHPUnit_Framework_Exception
{
    public function __construct($message, $code, $file, $line, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->file  = $file;
        $this->line  = $line;
    }
}
