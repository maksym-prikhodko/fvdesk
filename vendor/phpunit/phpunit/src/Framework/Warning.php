<?php
class PHPUnit_Framework_Warning extends PHPUnit_Framework_TestCase
{
    protected $message = '';
    protected $backupGlobals = false;
    protected $backupStaticAttributes = false;
    protected $runTestInSeparateProcess = false;
    protected $useErrorHandler = false;
    public function __construct($message = '')
    {
        $this->message = $message;
        parent::__construct('Warning');
    }
    protected function runTest()
    {
        $this->fail($this->message);
    }
    public function getMessage()
    {
        return $this->message;
    }
    public function toString()
    {
        return 'Warning';
    }
}
