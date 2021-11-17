<?php
class PHPUnit_Framework_SyntheticError extends PHPUnit_Framework_AssertionFailedError
{
    protected $syntheticFile = '';
    protected $syntheticLine = 0;
    protected $syntheticTrace = array();
    public function __construct($message, $code, $file, $line, $trace)
    {
        parent::__construct($message, $code);
        $this->syntheticFile  = $file;
        $this->syntheticLine  = $line;
        $this->syntheticTrace = $trace;
    }
    public function getSyntheticFile()
    {
        return $this->syntheticFile;
    }
    public function getSyntheticLine()
    {
        return $this->syntheticLine;
    }
    public function getSyntheticTrace()
    {
        return $this->syntheticTrace;
    }
}
