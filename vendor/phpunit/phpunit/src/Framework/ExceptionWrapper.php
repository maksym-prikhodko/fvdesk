<?php
class PHPUnit_Framework_ExceptionWrapper extends PHPUnit_Framework_Exception
{
    protected $classname;
    protected $previous;
    public function __construct(Exception $e)
    {
        parent::__construct($e->getMessage(), (int) $e->getCode());
        $this->classname = get_class($e);
        $this->file = $e->getFile();
        $this->line = $e->getLine();
        $this->serializableTrace = $e->getTrace();
        foreach ($this->serializableTrace as $i => $call) {
            unset($this->serializableTrace[$i]['args']);
        }
        if ($e->getPrevious()) {
            $this->previous = new self($e->getPrevious());
        }
    }
    public function getClassname()
    {
        return $this->classname;
    }
    public function getPreviousWrapped()
    {
        return $this->previous;
    }
    public function __toString()
    {
        $string = PHPUnit_Framework_TestFailure::exceptionToString($this);
        if ($trace = PHPUnit_Util_Filter::getFilteredStacktrace($this)) {
            $string .= "\n" . $trace;
        }
        if ($this->previous) {
            $string .= "\nCaused by\n" . $this->previous;
        }
        return $string;
    }
}
