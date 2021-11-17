<?php
if (!defined('JSON_PRETTY_PRINT')) {
    define('JSON_PRETTY_PRINT', 128);
}
class PHPUnit_Util_Log_JSON extends PHPUnit_Util_Printer implements PHPUnit_Framework_TestListener
{
    protected $currentTestSuiteName = '';
    protected $currentTestName = '';
    protected $currentTestPass = true;
    public function addError(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        $this->writeCase(
            'error',
            $time,
            PHPUnit_Util_Filter::getFilteredStacktrace($e, false),
            $e->getMessage(),
            $test
        );
        $this->currentTestPass = false;
    }
    public function addFailure(PHPUnit_Framework_Test $test, PHPUnit_Framework_AssertionFailedError $e, $time)
    {
        $this->writeCase(
            'fail',
            $time,
            PHPUnit_Util_Filter::getFilteredStacktrace($e, false),
            $e->getMessage(),
            $test
        );
        $this->currentTestPass = false;
    }
    public function addIncompleteTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        $this->writeCase(
            'error',
            $time,
            PHPUnit_Util_Filter::getFilteredStacktrace($e, false),
            'Incomplete Test: ' . $e->getMessage(),
            $test
        );
        $this->currentTestPass = false;
    }
    public function addRiskyTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        $this->writeCase(
            'error',
            $time,
            PHPUnit_Util_Filter::getFilteredStacktrace($e, false),
            'Risky Test: ' . $e->getMessage(),
            $test
        );
        $this->currentTestPass = false;
    }
    public function addSkippedTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        $this->writeCase(
            'error',
            $time,
            PHPUnit_Util_Filter::getFilteredStacktrace($e, false),
            'Skipped Test: ' . $e->getMessage(),
            $test
        );
        $this->currentTestPass = false;
    }
    public function startTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
        $this->currentTestSuiteName = $suite->getName();
        $this->currentTestName      = '';
        $this->write(
            array(
            'event' => 'suiteStart',
            'suite' => $this->currentTestSuiteName,
            'tests' => count($suite)
            )
        );
    }
    public function endTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
        $this->currentTestSuiteName = '';
        $this->currentTestName      = '';
    }
    public function startTest(PHPUnit_Framework_Test $test)
    {
        $this->currentTestName = PHPUnit_Util_Test::describe($test);
        $this->currentTestPass = true;
        $this->write(
            array(
            'event' => 'testStart',
            'suite' => $this->currentTestSuiteName,
            'test'  => $this->currentTestName
            )
        );
    }
    public function endTest(PHPUnit_Framework_Test $test, $time)
    {
        if ($this->currentTestPass) {
            $this->writeCase('pass', $time, array(), '', $test);
        }
    }
    protected function writeCase($status, $time, array $trace = array(), $message = '', $test = null)
    {
        $output = '';
        if ($test !== null && method_exists($test, 'hasOutput') && $test->hasOutput()) {
            $output = $test->getActualOutput();
        }
        $this->write(
            array(
            'event'   => 'test',
            'suite'   => $this->currentTestSuiteName,
            'test'    => $this->currentTestName,
            'status'  => $status,
            'time'    => $time,
            'trace'   => $trace,
            'message' => PHPUnit_Util_String::convertToUtf8($message),
            'output'  => $output,
            )
        );
    }
    public function write($buffer)
    {
        array_walk_recursive($buffer, function (&$input) {
            if (is_string($input)) {
                $input = PHPUnit_Util_String::convertToUtf8($input);
            }
        });
        parent::write(json_encode($buffer, JSON_PRETTY_PRINT));
    }
}
