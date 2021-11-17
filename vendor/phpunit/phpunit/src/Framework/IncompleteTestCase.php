<?php
class PHPUnit_Framework_IncompleteTestCase extends PHPUnit_Framework_TestCase
{
    protected $message = '';
    protected $backupGlobals = false;
    protected $backupStaticAttributes = false;
    protected $runTestInSeparateProcess = false;
    protected $useErrorHandler = false;
    protected $useOutputBuffering = false;
    public function __construct($className, $methodName, $message = '')
    {
        $this->message = $message;
        parent::__construct($className . '::' . $methodName);
    }
    protected function runTest()
    {
        $this->markTestIncomplete($this->message);
    }
    public function getMessage()
    {
        return $this->message;
    }
    public function toString()
    {
        return $this->getName();
    }
}
