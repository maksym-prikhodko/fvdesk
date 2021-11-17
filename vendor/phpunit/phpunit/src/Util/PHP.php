<?php
abstract class PHPUnit_Util_PHP
{
    public static function factory()
    {
        if (DIRECTORY_SEPARATOR == '\\') {
            return new PHPUnit_Util_PHP_Windows;
        }
        return new PHPUnit_Util_PHP_Default;
    }
    public function runTestJob($job, PHPUnit_Framework_Test $test, PHPUnit_Framework_TestResult $result)
    {
        $result->startTest($test);
        $_result = $this->runJob($job);
        $this->processChildResult(
            $test,
            $result,
            $_result['stdout'],
            $_result['stderr']
        );
    }
    abstract public function runJob($job, array $settings = array());
    protected function settingsToParameters(array $settings)
    {
        $buffer = '';
        foreach ($settings as $setting) {
            $buffer .= ' -d ' . $setting;
        }
        return $buffer;
    }
    private function processChildResult(PHPUnit_Framework_Test $test, PHPUnit_Framework_TestResult $result, $stdout, $stderr)
    {
        $time = 0;
        if (!empty($stderr)) {
            $result->addError(
                $test,
                new PHPUnit_Framework_Exception(trim($stderr)),
                $time
            );
        } else {
            set_error_handler(function ($errno, $errstr, $errfile, $errline) {
                throw new ErrorException($errstr, $errno, $errno, $errfile, $errline);
            });
            try {
                if (strpos($stdout, "#!/usr/bin/env php\n") === 0) {
                    $stdout = substr($stdout, 19);
                }
                $childResult = unserialize(str_replace("#!/usr/bin/env php\n", '', $stdout));
                restore_error_handler();
            } catch (ErrorException $e) {
                restore_error_handler();
                $childResult = false;
                $result->addError(
                    $test,
                    new PHPUnit_Framework_Exception(trim($stdout), 0, $e),
                    $time
                );
            }
            if ($childResult !== false) {
                if (!empty($childResult['output'])) {
                    $output = $childResult['output'];
                }
                $test->setResult($childResult['testResult']);
                $test->addToAssertionCount($childResult['numAssertions']);
                $childResult = $childResult['result'];
                if ($result->getCollectCodeCoverageInformation()) {
                    $result->getCodeCoverage()->merge(
                        $childResult->getCodeCoverage()
                    );
                }
                $time           = $childResult->time();
                $notImplemented = $childResult->notImplemented();
                $risky          = $childResult->risky();
                $skipped        = $childResult->skipped();
                $errors         = $childResult->errors();
                $failures       = $childResult->failures();
                if (!empty($notImplemented)) {
                    $result->addError(
                        $test,
                        $this->getException($notImplemented[0]),
                        $time
                    );
                } elseif (!empty($risky)) {
                    $result->addError(
                        $test,
                        $this->getException($risky[0]),
                        $time
                    );
                } elseif (!empty($skipped)) {
                    $result->addError(
                        $test,
                        $this->getException($skipped[0]),
                        $time
                    );
                } elseif (!empty($errors)) {
                    $result->addError(
                        $test,
                        $this->getException($errors[0]),
                        $time
                    );
                } elseif (!empty($failures)) {
                    $result->addFailure(
                        $test,
                        $this->getException($failures[0]),
                        $time
                    );
                }
            }
        }
        $result->endTest($test, $time);
        if (!empty($output)) {
            print $output;
        }
    }
    private function getException(PHPUnit_Framework_TestFailure $error)
    {
        $exception = $error->thrownException();
        if ($exception instanceof __PHP_Incomplete_Class) {
            $exceptionArray = array();
            foreach ((array) $exception as $key => $value) {
                $key = substr($key, strrpos($key, "\0") + 1);
                $exceptionArray[$key] = $value;
            }
            $exception = new PHPUnit_Framework_SyntheticError(
                sprintf(
                    '%s: %s',
                    $exceptionArray['_PHP_Incomplete_Class_Name'],
                    $exceptionArray['message']
                ),
                $exceptionArray['code'],
                $exceptionArray['file'],
                $exceptionArray['line'],
                $exceptionArray['trace']
            );
        }
        return $exception;
    }
}
