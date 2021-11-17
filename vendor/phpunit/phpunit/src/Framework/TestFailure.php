<?php
class PHPUnit_Framework_TestFailure
{
    private $testName;
    protected $failedTest;
    protected $thrownException;
    public function __construct(PHPUnit_Framework_Test $failedTest, Exception $thrownException)
    {
        if ($failedTest instanceof PHPUnit_Framework_SelfDescribing) {
            $this->testName = $failedTest->toString();
        } else {
            $this->testName = get_class($failedTest);
        }
        if (!$failedTest instanceof PHPUnit_Framework_TestCase || !$failedTest->isInIsolation()) {
            $this->failedTest = $failedTest;
        }
        $this->thrownException = $thrownException;
    }
    public function toString()
    {
        return sprintf(
            '%s: %s',
            $this->testName,
            $this->thrownException->getMessage()
        );
    }
    public function getExceptionAsString()
    {
        return self::exceptionToString($this->thrownException);
    }
    public static function exceptionToString(Exception $e)
    {
        if ($e instanceof PHPUnit_Framework_SelfDescribing) {
            $buffer = $e->toString();
            if ($e instanceof PHPUnit_Framework_ExpectationFailedException && $e->getComparisonFailure()) {
                $buffer = $buffer . $e->getComparisonFailure()->getDiff();
            }
            if (!empty($buffer)) {
                $buffer = trim($buffer) . "\n";
            }
        } elseif ($e instanceof PHPUnit_Framework_Error) {
            $buffer = $e->getMessage() . "\n";
        } elseif ($e instanceof PHPUnit_Framework_ExceptionWrapper) {
            $buffer = $e->getClassname() . ': ' . $e->getMessage() . "\n";
        } else {
            $buffer = get_class($e) . ': ' . $e->getMessage() . "\n";
        }
        return $buffer;
    }
    public function getTestName()
    {
        return $this->testName;
    }
    public function failedTest()
    {
        return $this->failedTest;
    }
    public function thrownException()
    {
        return $this->thrownException;
    }
    public function exceptionMessage()
    {
        return $this->thrownException()->getMessage();
    }
    public function isFailure()
    {
        return ($this->thrownException() instanceof PHPUnit_Framework_AssertionFailedError);
    }
}
