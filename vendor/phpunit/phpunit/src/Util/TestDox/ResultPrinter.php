<?php
abstract class PHPUnit_Util_TestDox_ResultPrinter extends PHPUnit_Util_Printer implements PHPUnit_Framework_TestListener
{
    protected $prettifier;
    protected $testClass = '';
    protected $testStatus = false;
    protected $tests = array();
    protected $successful = 0;
    protected $failed = 0;
    protected $risky = 0;
    protected $skipped = 0;
    protected $incomplete = 0;
    protected $testTypeOfInterest = 'PHPUnit_Framework_TestCase';
    protected $currentTestClassPrettified;
    protected $currentTestMethodPrettified;
    public function __construct($out = null)
    {
        parent::__construct($out);
        $this->prettifier = new PHPUnit_Util_TestDox_NamePrettifier;
        $this->startRun();
    }
    public function flush()
    {
        $this->doEndClass();
        $this->endRun();
        parent::flush();
    }
    public function addError(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        if ($test instanceof $this->testTypeOfInterest) {
            $this->testStatus = PHPUnit_Runner_BaseTestRunner::STATUS_ERROR;
            $this->failed++;
        }
    }
    public function addFailure(PHPUnit_Framework_Test $test, PHPUnit_Framework_AssertionFailedError $e, $time)
    {
        if ($test instanceof $this->testTypeOfInterest) {
            $this->testStatus = PHPUnit_Runner_BaseTestRunner::STATUS_FAILURE;
            $this->failed++;
        }
    }
    public function addIncompleteTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        if ($test instanceof $this->testTypeOfInterest) {
            $this->testStatus = PHPUnit_Runner_BaseTestRunner::STATUS_INCOMPLETE;
            $this->incomplete++;
        }
    }
    public function addRiskyTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        if ($test instanceof $this->testTypeOfInterest) {
            $this->testStatus = PHPUnit_Runner_BaseTestRunner::STATUS_RISKY;
            $this->risky++;
        }
    }
    public function addSkippedTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        if ($test instanceof $this->testTypeOfInterest) {
            $this->testStatus = PHPUnit_Runner_BaseTestRunner::STATUS_SKIPPED;
            $this->skipped++;
        }
    }
    public function startTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
    }
    public function endTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
    }
    public function startTest(PHPUnit_Framework_Test $test)
    {
        if ($test instanceof $this->testTypeOfInterest) {
            $class = get_class($test);
            if ($this->testClass != $class) {
                if ($this->testClass != '') {
                    $this->doEndClass();
                }
                $this->currentTestClassPrettified = $this->prettifier->prettifyTestClass($class);
                $this->startClass($class);
                $this->testClass = $class;
                $this->tests     = array();
            }
            $prettified = false;
            if ($test instanceof PHPUnit_Framework_TestCase &&
               !$test instanceof PHPUnit_Framework_Warning) {
                $annotations = $test->getAnnotations();
                if (isset($annotations['method']['testdox'][0])) {
                    $this->currentTestMethodPrettified = $annotations['method']['testdox'][0];
                    $prettified                        = true;
                }
            }
            if (!$prettified) {
                $this->currentTestMethodPrettified = $this->prettifier->prettifyTestMethod($test->getName(false));
            }
            $this->testStatus = PHPUnit_Runner_BaseTestRunner::STATUS_PASSED;
        }
    }
    public function endTest(PHPUnit_Framework_Test $test, $time)
    {
        if ($test instanceof $this->testTypeOfInterest) {
            if (!isset($this->tests[$this->currentTestMethodPrettified])) {
                if ($this->testStatus == PHPUnit_Runner_BaseTestRunner::STATUS_PASSED) {
                    $this->tests[$this->currentTestMethodPrettified]['success'] = 1;
                    $this->tests[$this->currentTestMethodPrettified]['failure'] = 0;
                } else {
                    $this->tests[$this->currentTestMethodPrettified]['success'] = 0;
                    $this->tests[$this->currentTestMethodPrettified]['failure'] = 1;
                }
            } else {
                if ($this->testStatus == PHPUnit_Runner_BaseTestRunner::STATUS_PASSED) {
                    $this->tests[$this->currentTestMethodPrettified]['success']++;
                } else {
                    $this->tests[$this->currentTestMethodPrettified]['failure']++;
                }
            }
            $this->currentTestClassPrettified  = null;
            $this->currentTestMethodPrettified = null;
        }
    }
    protected function doEndClass()
    {
        foreach ($this->tests as $name => $data) {
            $this->onTest($name, $data['failure'] == 0);
        }
        $this->endClass($this->testClass);
    }
    protected function startRun()
    {
    }
    protected function startClass($name)
    {
    }
    protected function onTest($name, $success = true)
    {
    }
    protected function endClass($name)
    {
    }
    protected function endRun()
    {
    }
}
