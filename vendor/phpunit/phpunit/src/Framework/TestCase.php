<?php
use SebastianBergmann\GlobalState\Snapshot;
use SebastianBergmann\GlobalState\Restorer;
use SebastianBergmann\GlobalState\Blacklist;
use SebastianBergmann\Diff\Differ;
use SebastianBergmann\Exporter\Exporter;
use Prophecy\Exception\Prediction\PredictionException;
use Prophecy\Prophet;
abstract class PHPUnit_Framework_TestCase extends PHPUnit_Framework_Assert implements PHPUnit_Framework_Test, PHPUnit_Framework_SelfDescribing
{
    protected $backupGlobals = null;
    protected $backupGlobalsBlacklist = array();
    protected $backupStaticAttributes = null;
    protected $backupStaticAttributesBlacklist = array();
    protected $runTestInSeparateProcess = null;
    protected $preserveGlobalState = true;
    private $inIsolation = false;
    private $data = array();
    private $dataName = '';
    private $useErrorHandler = null;
    private $expectedException = null;
    private $expectedExceptionMessage = '';
    private $expectedExceptionMessageRegExp = '';
    private $expectedExceptionCode;
    private $name = null;
    private $dependencies = array();
    private $dependencyInput = array();
    private $iniSettings = array();
    private $locale = array();
    private $mockObjects = array();
    private $mockObjectGenerator = null;
    private $status;
    private $statusMessage = '';
    private $numAssertions = 0;
    private $result;
    private $testResult;
    private $output = '';
    private $outputExpectedRegex = null;
    private $outputExpectedString = null;
    private $outputCallback = false;
    private $outputBufferingActive = false;
    private $outputBufferingLevel;
    private $snapshot;
    private $prophet;
    private $disallowChangesToGlobalState = false;
    public function __construct($name = null, array $data = array(), $dataName = '')
    {
        if ($name !== null) {
            $this->setName($name);
        }
        $this->data                = $data;
        $this->dataName            = $dataName;
    }
    public function toString()
    {
        $class = new ReflectionClass($this);
        $buffer = sprintf(
            '%s::%s',
            $class->name,
            $this->getName(false)
        );
        return $buffer . $this->getDataSetAsString();
    }
    public function count()
    {
        return 1;
    }
    public function getAnnotations()
    {
        return PHPUnit_Util_Test::parseTestMethodAnnotations(
            get_class($this),
            $this->name
        );
    }
    public function getName($withDataSet = true)
    {
        if ($withDataSet) {
            return $this->name . $this->getDataSetAsString(false);
        } else {
            return $this->name;
        }
    }
    public function getSize()
    {
        return PHPUnit_Util_Test::getSize(
            get_class($this),
            $this->getName(false)
        );
    }
    public function getActualOutput()
    {
        if (!$this->outputBufferingActive) {
            return $this->output;
        } else {
            return ob_get_contents();
        }
    }
    public function hasOutput()
    {
        if (strlen($this->output) === 0) {
            return false;
        }
        if ($this->hasExpectationOnOutput()) {
            return false;
        }
        return true;
    }
    public function expectOutputRegex($expectedRegex)
    {
        if ($this->outputExpectedString !== null) {
            throw new PHPUnit_Framework_Exception;
        }
        if (is_string($expectedRegex) || is_null($expectedRegex)) {
            $this->outputExpectedRegex = $expectedRegex;
        }
    }
    public function expectOutputString($expectedString)
    {
        if ($this->outputExpectedRegex !== null) {
            throw new PHPUnit_Framework_Exception;
        }
        if (is_string($expectedString) || is_null($expectedString)) {
            $this->outputExpectedString = $expectedString;
        }
    }
    public function hasPerformedExpectationsOnOutput()
    {
        return $this->hasExpectationOnOutput();
    }
    public function hasExpectationOnOutput()
    {
        return is_string($this->outputExpectedString) || is_string($this->outputExpectedRegex);
    }
    public function getExpectedException()
    {
        return $this->expectedException;
    }
    public function setExpectedException($exceptionName, $exceptionMessage = '', $exceptionCode = null)
    {
        $this->expectedException        = $exceptionName;
        $this->expectedExceptionMessage = $exceptionMessage;
        $this->expectedExceptionCode    = $exceptionCode;
    }
    public function setExpectedExceptionRegExp($exceptionName, $exceptionMessageRegExp = '', $exceptionCode = null)
    {
        $this->expectedException              = $exceptionName;
        $this->expectedExceptionMessageRegExp = $exceptionMessageRegExp;
        $this->expectedExceptionCode          = $exceptionCode;
    }
    protected function setExpectedExceptionFromAnnotation()
    {
        try {
            $expectedException = PHPUnit_Util_Test::getExpectedException(
                get_class($this),
                $this->name
            );
            if ($expectedException !== false) {
                $this->setExpectedException(
                    $expectedException['class'],
                    $expectedException['message'],
                    $expectedException['code']
                );
                if (!empty($expectedException['message_regex'])) {
                    $this->setExpectedExceptionRegExp(
                        $expectedException['class'],
                        $expectedException['message_regex'],
                        $expectedException['code']
                    );
                }
            }
        } catch (ReflectionException $e) {
        }
    }
    public function setUseErrorHandler($useErrorHandler)
    {
        $this->useErrorHandler = $useErrorHandler;
    }
    protected function setUseErrorHandlerFromAnnotation()
    {
        try {
            $useErrorHandler = PHPUnit_Util_Test::getErrorHandlerSettings(
                get_class($this),
                $this->name
            );
            if ($useErrorHandler !== null) {
                $this->setUseErrorHandler($useErrorHandler);
            }
        } catch (ReflectionException $e) {
        }
    }
    protected function checkRequirements()
    {
        if (!$this->name || !method_exists($this, $this->name)) {
            return;
        }
        $missingRequirements = PHPUnit_Util_Test::getMissingRequirements(
            get_class($this),
            $this->name
        );
        if ($missingRequirements) {
            $this->markTestSkipped(implode(PHP_EOL, $missingRequirements));
        }
    }
    public function getStatus()
    {
        return $this->status;
    }
    public function getStatusMessage()
    {
        return $this->statusMessage;
    }
    public function hasFailed()
    {
        $status = $this->getStatus();
        return $status == PHPUnit_Runner_BaseTestRunner::STATUS_FAILURE ||
               $status == PHPUnit_Runner_BaseTestRunner::STATUS_ERROR;
    }
    public function run(PHPUnit_Framework_TestResult $result = null)
    {
        if ($result === null) {
            $result = $this->createResult();
        }
        if (!$this instanceof PHPUnit_Framework_Warning) {
            $this->setTestResultObject($result);
            $this->setUseErrorHandlerFromAnnotation();
        }
        if ($this->useErrorHandler !== null) {
            $oldErrorHandlerSetting = $result->getConvertErrorsToExceptions();
            $result->convertErrorsToExceptions($this->useErrorHandler);
        }
        if (!$this instanceof PHPUnit_Framework_Warning && !$this->handleDependencies()) {
            return;
        }
        if ($this->runTestInSeparateProcess === true &&
            $this->inIsolation !== true &&
            !$this instanceof PHPUnit_Extensions_SeleniumTestCase &&
            !$this instanceof PHPUnit_Extensions_PhptTestCase) {
            $class = new ReflectionClass($this);
            $template = new Text_Template(
                __DIR__ . '/../Util/PHP/Template/TestCaseMethod.tpl'
            );
            if ($this->preserveGlobalState) {
                $constants     = PHPUnit_Util_GlobalState::getConstantsAsString();
                $globals       = PHPUnit_Util_GlobalState::getGlobalsAsString();
                $includedFiles = PHPUnit_Util_GlobalState::getIncludedFilesAsString();
                $iniSettings   = PHPUnit_Util_GlobalState::getIniSettingsAsString();
            } else {
                $constants     = '';
                if (!empty($GLOBALS['__PHPUNIT_BOOTSTRAP'])) {
                    $globals     = '$GLOBALS[\'__PHPUNIT_BOOTSTRAP\'] = ' . var_export($GLOBALS['__PHPUNIT_BOOTSTRAP'], true) . ";\n";
                } else {
                    $globals     = '';
                }
                $includedFiles = '';
                $iniSettings   = '';
            }
            $coverage                                = $result->getCollectCodeCoverageInformation()       ? 'true' : 'false';
            $isStrictAboutTestsThatDoNotTestAnything = $result->isStrictAboutTestsThatDoNotTestAnything() ? 'true' : 'false';
            $isStrictAboutOutputDuringTests          = $result->isStrictAboutOutputDuringTests()          ? 'true' : 'false';
            $isStrictAboutTestSize                   = $result->isStrictAboutTestSize()                   ? 'true' : 'false';
            $isStrictAboutTodoAnnotatedTests         = $result->isStrictAboutTodoAnnotatedTests()         ? 'true' : 'false';
            if (defined('PHPUNIT_COMPOSER_INSTALL')) {
                $composerAutoload = var_export(PHPUNIT_COMPOSER_INSTALL, true);
            } else {
                $composerAutoload = '\'\'';
            }
            if (defined('__PHPUNIT_PHAR__')) {
                $phar = var_export(__PHPUNIT_PHAR__, true);
            } else {
                $phar = '\'\'';
            }
            $data            = var_export(serialize($this->data), true);
            $dataName        = var_export($this->dataName, true);
            $dependencyInput = var_export(serialize($this->dependencyInput), true);
            $includePath     = var_export(get_include_path(), true);
            $data            = "'." . $data . ".'";
            $dataName        = "'.(" . $dataName . ").'";
            $dependencyInput = "'." . $dependencyInput . ".'";
            $includePath     = "'." . $includePath . ".'";
            $template->setVar(
                array(
                'composerAutoload'                        => $composerAutoload,
                'phar'                                    => $phar,
                'filename'                                => $class->getFileName(),
                'className'                               => $class->getName(),
                'methodName'                              => $this->name,
                'collectCodeCoverageInformation'          => $coverage,
                'data'                                    => $data,
                'dataName'                                => $dataName,
                'dependencyInput'                         => $dependencyInput,
                'constants'                               => $constants,
                'globals'                                 => $globals,
                'include_path'                            => $includePath,
                'included_files'                          => $includedFiles,
                'iniSettings'                             => $iniSettings,
                'isStrictAboutTestsThatDoNotTestAnything' => $isStrictAboutTestsThatDoNotTestAnything,
                'isStrictAboutOutputDuringTests'          => $isStrictAboutOutputDuringTests,
                'isStrictAboutTestSize'                   => $isStrictAboutTestSize,
                'isStrictAboutTodoAnnotatedTests'         => $isStrictAboutTodoAnnotatedTests
                )
            );
            $this->prepareTemplate($template);
            $php = PHPUnit_Util_PHP::factory();
            $php->runTestJob($template->render(), $this, $result);
        } else {
            $result->run($this);
        }
        if ($this->useErrorHandler !== null) {
            $result->convertErrorsToExceptions($oldErrorHandlerSetting);
        }
        $this->result = null;
        return $result;
    }
    public function runBare()
    {
        $this->numAssertions = 0;
        $this->snapshotGlobalState();
        $this->startOutputBuffering();
        clearstatcache();
        $currentWorkingDirectory = getcwd();
        $hookMethods = PHPUnit_Util_Test::getHookMethods(get_class($this));
        try {
            $hasMetRequirements = false;
            $this->checkRequirements();
            $hasMetRequirements = true;
            if ($this->inIsolation) {
                foreach ($hookMethods['beforeClass'] as $method) {
                    $this->$method();
                }
            }
            $this->setExpectedExceptionFromAnnotation();
            foreach ($hookMethods['before'] as $method) {
                $this->$method();
            }
            $this->assertPreConditions();
            $this->testResult = $this->runTest();
            $this->verifyMockObjects();
            $this->assertPostConditions();
            $this->status = PHPUnit_Runner_BaseTestRunner::STATUS_PASSED;
        } catch (PHPUnit_Framework_IncompleteTest $e) {
            $this->status        = PHPUnit_Runner_BaseTestRunner::STATUS_INCOMPLETE;
            $this->statusMessage = $e->getMessage();
        } catch (PHPUnit_Framework_SkippedTest $e) {
            $this->status        = PHPUnit_Runner_BaseTestRunner::STATUS_SKIPPED;
            $this->statusMessage = $e->getMessage();
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            $this->status = PHPUnit_Runner_BaseTestRunner::STATUS_FAILURE;
            $this->statusMessage = $e->getMessage();
        } catch (PredictionException $e) {
            $this->status        = PHPUnit_Runner_BaseTestRunner::STATUS_FAILURE;
            $this->statusMessage = $e->getMessage();
        } catch (Exception $e) {
            $this->status        = PHPUnit_Runner_BaseTestRunner::STATUS_ERROR;
            $this->statusMessage = $e->getMessage();
        }
        $this->mockObjects = array();
        $this->prophet     = null;
        try {
            if ($hasMetRequirements) {
                foreach ($hookMethods['after'] as $method) {
                    $this->$method();
                }
                if ($this->inIsolation) {
                    foreach ($hookMethods['afterClass'] as $method) {
                        $this->$method();
                    }
                }
            }
        } catch (Exception $_e) {
            if (!isset($e)) {
                $e = $_e;
            }
        }
        try {
            $this->stopOutputBuffering();
        } catch (PHPUnit_Framework_RiskyTestError $_e) {
            if (!isset($e)) {
                $e = $_e;
            }
        }
        clearstatcache();
        if ($currentWorkingDirectory != getcwd()) {
            chdir($currentWorkingDirectory);
        }
        $this->restoreGlobalState();
        foreach ($this->iniSettings as $varName => $oldValue) {
            ini_set($varName, $oldValue);
        }
        $this->iniSettings = array();
        foreach ($this->locale as $category => $locale) {
            setlocale($category, $locale);
        }
        if (!isset($e)) {
            try {
                if ($this->outputExpectedRegex !== null) {
                    $this->assertRegExp($this->outputExpectedRegex, $this->output);
                } elseif ($this->outputExpectedString !== null) {
                    $this->assertEquals($this->outputExpectedString, $this->output);
                }
            } catch (Exception $_e) {
                $e = $_e;
            }
        }
        if (isset($e)) {
            if ($e instanceof PredictionException) {
                $e = new PHPUnit_Framework_AssertionFailedError($e->getMessage());
            }
            $this->onNotSuccessfulTest($e);
        }
    }
    protected function runTest()
    {
        if ($this->name === null) {
            throw new PHPUnit_Framework_Exception(
                'PHPUnit_Framework_TestCase::$name must not be null.'
            );
        }
        try {
            $class  = new ReflectionClass($this);
            $method = $class->getMethod($this->name);
        } catch (ReflectionException $e) {
            $this->fail($e->getMessage());
        }
        try {
            $testResult = $method->invokeArgs(
                $this,
                array_merge($this->data, $this->dependencyInput)
            );
        } catch (Exception $e) {
            $checkException = false;
            if (is_string($this->expectedException)) {
                $checkException = true;
                if ($e instanceof PHPUnit_Framework_Exception) {
                    $checkException = false;
                }
                $reflector = new ReflectionClass($this->expectedException);
                if ($this->expectedException == 'PHPUnit_Framework_Exception' ||
                    $reflector->isSubclassOf('PHPUnit_Framework_Exception')) {
                    $checkException = true;
                }
            }
            if ($checkException) {
                $this->assertThat(
                    $e,
                    new PHPUnit_Framework_Constraint_Exception(
                        $this->expectedException
                    )
                );
                if (is_string($this->expectedExceptionMessage) &&
                    !empty($this->expectedExceptionMessage)) {
                    $this->assertThat(
                        $e,
                        new PHPUnit_Framework_Constraint_ExceptionMessage(
                            $this->expectedExceptionMessage
                        )
                    );
                }
                if (is_string($this->expectedExceptionMessageRegExp) &&
                    !empty($this->expectedExceptionMessageRegExp)) {
                    $this->assertThat(
                        $e,
                        new PHPUnit_Framework_Constraint_ExceptionMessageRegExp(
                            $this->expectedExceptionMessageRegExp
                        )
                    );
                }
                if ($this->expectedExceptionCode !== null) {
                    $this->assertThat(
                        $e,
                        new PHPUnit_Framework_Constraint_ExceptionCode(
                            $this->expectedExceptionCode
                        )
                    );
                }
                return;
            } else {
                throw $e;
            }
        }
        if ($this->expectedException !== null) {
            $this->assertThat(
                null,
                new PHPUnit_Framework_Constraint_Exception(
                    $this->expectedException
                )
            );
        }
        return $testResult;
    }
    protected function verifyMockObjects()
    {
        foreach ($this->mockObjects as $mockObject) {
            if ($mockObject->__phpunit_hasMatchers()) {
                $this->numAssertions++;
            }
            $mockObject->__phpunit_verify();
        }
        if ($this->prophet !== null) {
            try {
                $this->prophet->checkPredictions();
            } catch (Exception $e) {
            }
            foreach ($this->prophet->getProphecies() as $objectProphecy) {
                foreach ($objectProphecy->getMethodProphecies() as $methodProphecies) {
                    foreach ($methodProphecies as $methodProphecy) {
                        $this->numAssertions += count($methodProphecy->getCheckedPredictions());
                    }
                }
            }
            if (isset($e)) {
                throw $e;
            }
        }
    }
    public function setName($name)
    {
        $this->name = $name;
    }
    public function setDependencies(array $dependencies)
    {
        $this->dependencies = $dependencies;
    }
    public function hasDependencies()
    {
        return count($this->dependencies) > 0;
    }
    public function setDependencyInput(array $dependencyInput)
    {
        $this->dependencyInput = $dependencyInput;
    }
    public function setDisallowChangesToGlobalState($disallowChangesToGlobalState)
    {
        $this->disallowChangesToGlobalState = $disallowChangesToGlobalState;
    }
    public function setBackupGlobals($backupGlobals)
    {
        if (is_null($this->backupGlobals) && is_bool($backupGlobals)) {
            $this->backupGlobals = $backupGlobals;
        }
    }
    public function setBackupStaticAttributes($backupStaticAttributes)
    {
        if (is_null($this->backupStaticAttributes) &&
            is_bool($backupStaticAttributes)) {
            $this->backupStaticAttributes = $backupStaticAttributes;
        }
    }
    public function setRunTestInSeparateProcess($runTestInSeparateProcess)
    {
        if (is_bool($runTestInSeparateProcess)) {
            if ($this->runTestInSeparateProcess === null) {
                $this->runTestInSeparateProcess = $runTestInSeparateProcess;
            }
        } else {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'boolean');
        }
    }
    public function setPreserveGlobalState($preserveGlobalState)
    {
        if (is_bool($preserveGlobalState)) {
            $this->preserveGlobalState = $preserveGlobalState;
        } else {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'boolean');
        }
    }
    public function setInIsolation($inIsolation)
    {
        if (is_bool($inIsolation)) {
            $this->inIsolation = $inIsolation;
        } else {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'boolean');
        }
    }
    public function isInIsolation()
    {
        return $this->inIsolation;
    }
    public function getResult()
    {
        return $this->testResult;
    }
    public function setResult($result)
    {
        $this->testResult = $result;
    }
    public function setOutputCallback($callback)
    {
        if (!is_callable($callback)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'callback');
        }
        $this->outputCallback = $callback;
    }
    public function getTestResultObject()
    {
        return $this->result;
    }
    public function setTestResultObject(PHPUnit_Framework_TestResult $result)
    {
        $this->result = $result;
    }
    protected function iniSet($varName, $newValue)
    {
        if (!is_string($varName)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'string');
        }
        $currentValue = ini_set($varName, $newValue);
        if ($currentValue !== false) {
            $this->iniSettings[$varName] = $currentValue;
        } else {
            throw new PHPUnit_Framework_Exception(
                sprintf(
                    'INI setting "%s" could not be set to "%s".',
                    $varName,
                    $newValue
                )
            );
        }
    }
    protected function setLocale()
    {
        $args = func_get_args();
        if (count($args) < 2) {
            throw new PHPUnit_Framework_Exception;
        }
        $category = $args[0];
        $locale   = $args[1];
        $categories = array(
          LC_ALL, LC_COLLATE, LC_CTYPE, LC_MONETARY, LC_NUMERIC, LC_TIME
        );
        if (defined('LC_MESSAGES')) {
            $categories[] = LC_MESSAGES;
        }
        if (!in_array($category, $categories)) {
            throw new PHPUnit_Framework_Exception;
        }
        if (!is_array($locale) && !is_string($locale)) {
            throw new PHPUnit_Framework_Exception;
        }
        $this->locale[$category] = setlocale($category, null);
        $result = call_user_func_array('setlocale', $args);
        if ($result === false) {
            throw new PHPUnit_Framework_Exception(
                'The locale functionality is not implemented on your platform, ' .
                'the specified locale does not exist or the category name is ' .
                'invalid.'
            );
        }
    }
    public function getMock($originalClassName, $methods = array(), array $arguments = array(), $mockClassName = '', $callOriginalConstructor = true, $callOriginalClone = true, $callAutoload = true, $cloneArguments = false, $callOriginalMethods = false)
    {
        $mockObject = $this->getMockObjectGenerator()->getMock(
            $originalClassName,
            $methods,
            $arguments,
            $mockClassName,
            $callOriginalConstructor,
            $callOriginalClone,
            $callAutoload,
            $cloneArguments,
            $callOriginalMethods
        );
        $this->mockObjects[] = $mockObject;
        return $mockObject;
    }
    public function getMockBuilder($className)
    {
        return new PHPUnit_Framework_MockObject_MockBuilder($this, $className);
    }
    protected function getMockClass($originalClassName, $methods = array(), array $arguments = array(), $mockClassName = '', $callOriginalConstructor = false, $callOriginalClone = true, $callAutoload = true, $cloneArguments = false)
    {
        $mock = $this->getMock(
            $originalClassName,
            $methods,
            $arguments,
            $mockClassName,
            $callOriginalConstructor,
            $callOriginalClone,
            $callAutoload,
            $cloneArguments
        );
        return get_class($mock);
    }
    public function getMockForAbstractClass($originalClassName, array $arguments = array(), $mockClassName = '', $callOriginalConstructor = true, $callOriginalClone = true, $callAutoload = true, $mockedMethods = array(), $cloneArguments = false)
    {
        $mockObject = $this->getMockObjectGenerator()->getMockForAbstractClass(
            $originalClassName,
            $arguments,
            $mockClassName,
            $callOriginalConstructor,
            $callOriginalClone,
            $callAutoload,
            $mockedMethods,
            $cloneArguments
        );
        $this->mockObjects[] = $mockObject;
        return $mockObject;
    }
    protected function getMockFromWsdl($wsdlFile, $originalClassName = '', $mockClassName = '', array $methods = array(), $callOriginalConstructor = true, array $options = array())
    {
        if ($originalClassName === '') {
            $originalClassName = str_replace('.wsdl', '', basename($wsdlFile));
        }
        if (!class_exists($originalClassName)) {
            eval(
            $this->getMockObjectGenerator()->generateClassFromWsdl(
                $wsdlFile,
                $originalClassName,
                $methods,
                $options
            )
            );
        }
        return $this->getMock(
            $originalClassName,
            $methods,
            array('', $options),
            $mockClassName,
            $callOriginalConstructor,
            false,
            false
        );
    }
    public function getMockForTrait($traitName, array $arguments = array(), $mockClassName = '', $callOriginalConstructor = true, $callOriginalClone = true, $callAutoload = true, $mockedMethods = array(), $cloneArguments = false)
    {
        $mockObject = $this->getMockObjectGenerator()->getMockForTrait(
            $traitName,
            $arguments,
            $mockClassName,
            $callOriginalConstructor,
            $callOriginalClone,
            $callAutoload,
            $mockedMethods,
            $cloneArguments
        );
        $this->mockObjects[] = $mockObject;
        return $mockObject;
    }
    protected function getObjectForTrait($traitName, array $arguments = array(), $traitClassName = '', $callOriginalConstructor = true, $callOriginalClone = true, $callAutoload = true, $cloneArguments = false)
    {
        return $this->getMockObjectGenerator()->getObjectForTrait(
            $traitName,
            $arguments,
            $traitClassName,
            $callOriginalConstructor,
            $callOriginalClone,
            $callAutoload,
            $cloneArguments
        );
    }
    protected function prophesize($classOrInterface = null)
    {
        return $this->getProphet()->prophesize($classOrInterface);
    }
    public function addToAssertionCount($count)
    {
        $this->numAssertions += $count;
    }
    public function getNumAssertions()
    {
        return $this->numAssertions;
    }
    public static function any()
    {
        return new PHPUnit_Framework_MockObject_Matcher_AnyInvokedCount;
    }
    public static function never()
    {
        return new PHPUnit_Framework_MockObject_Matcher_InvokedCount(0);
    }
    public static function atLeast($requiredInvocations)
    {
        return new PHPUnit_Framework_MockObject_Matcher_InvokedAtLeastCount(
            $requiredInvocations
        );
    }
    public static function atLeastOnce()
    {
        return new PHPUnit_Framework_MockObject_Matcher_InvokedAtLeastOnce;
    }
    public static function once()
    {
        return new PHPUnit_Framework_MockObject_Matcher_InvokedCount(1);
    }
    public static function exactly($count)
    {
        return new PHPUnit_Framework_MockObject_Matcher_InvokedCount($count);
    }
    public static function atMost($allowedInvocations)
    {
        return new PHPUnit_Framework_MockObject_Matcher_InvokedAtMostCount(
            $allowedInvocations
        );
    }
    public static function at($index)
    {
        return new PHPUnit_Framework_MockObject_Matcher_InvokedAtIndex($index);
    }
    public static function returnValue($value)
    {
        return new PHPUnit_Framework_MockObject_Stub_Return($value);
    }
    public static function returnValueMap(array $valueMap)
    {
        return new PHPUnit_Framework_MockObject_Stub_ReturnValueMap($valueMap);
    }
    public static function returnArgument($argumentIndex)
    {
        return new PHPUnit_Framework_MockObject_Stub_ReturnArgument(
            $argumentIndex
        );
    }
    public static function returnCallback($callback)
    {
        return new PHPUnit_Framework_MockObject_Stub_ReturnCallback($callback);
    }
    public static function returnSelf()
    {
        return new PHPUnit_Framework_MockObject_Stub_ReturnSelf();
    }
    public static function throwException(Exception $exception)
    {
        return new PHPUnit_Framework_MockObject_Stub_Exception($exception);
    }
    public static function onConsecutiveCalls()
    {
        $args = func_get_args();
        return new PHPUnit_Framework_MockObject_Stub_ConsecutiveCalls($args);
    }
    protected function getDataSetAsString($includeData = true)
    {
        $buffer = '';
        if (!empty($this->data)) {
            if (is_int($this->dataName)) {
                $buffer .= sprintf(' with data set #%d', $this->dataName);
            } else {
                $buffer .= sprintf(' with data set "%s"', $this->dataName);
            }
            $exporter = new Exporter;
            if ($includeData) {
                $buffer .= sprintf(' (%s)', $exporter->shortenedRecursiveExport($this->data));
            }
        }
        return $buffer;
    }
    protected function createResult()
    {
        return new PHPUnit_Framework_TestResult;
    }
    protected function handleDependencies()
    {
        if (!empty($this->dependencies) && !$this->inIsolation) {
            $className  = get_class($this);
            $passed     = $this->result->passed();
            $passedKeys = array_keys($passed);
            $numKeys    = count($passedKeys);
            for ($i = 0; $i < $numKeys; $i++) {
                $pos = strpos($passedKeys[$i], ' with data set');
                if ($pos !== false) {
                    $passedKeys[$i] = substr($passedKeys[$i], 0, $pos);
                }
            }
            $passedKeys = array_flip(array_unique($passedKeys));
            foreach ($this->dependencies as $dependency) {
                if (strpos($dependency, '::') === false) {
                    $dependency = $className . '::' . $dependency;
                }
                if (!isset($passedKeys[$dependency])) {
                    $this->result->addError(
                        $this,
                        new PHPUnit_Framework_SkippedTestError(
                            sprintf(
                                'This test depends on "%s" to pass.',
                                $dependency
                            )
                        ),
                        0
                    );
                    return false;
                }
                if (isset($passed[$dependency])) {
                    if ($passed[$dependency]['size'] > $this->getSize()) {
                        $this->result->addError(
                            $this,
                            new PHPUnit_Framework_SkippedTestError(
                                'This test depends on a test that is larger than itself.'
                            ),
                            0
                        );
                        return false;
                    }
                    $this->dependencyInput[$dependency] = $passed[$dependency]['result'];
                } else {
                    $this->dependencyInput[$dependency] = null;
                }
            }
        }
        return true;
    }
    public static function setUpBeforeClass()
    {
    }
    protected function setUp()
    {
    }
    protected function assertPreConditions()
    {
    }
    protected function assertPostConditions()
    {
    }
    protected function tearDown()
    {
    }
    public static function tearDownAfterClass()
    {
    }
    protected function onNotSuccessfulTest(Exception $e)
    {
        throw $e;
    }
    protected function prepareTemplate(Text_Template $template)
    {
    }
    protected function getMockObjectGenerator()
    {
        if (null === $this->mockObjectGenerator) {
            $this->mockObjectGenerator = new PHPUnit_Framework_MockObject_Generator;
        }
        return $this->mockObjectGenerator;
    }
    private function startOutputBuffering()
    {
        while (!defined('PHPUNIT_TESTSUITE') && ob_get_level() > 0) {
            ob_end_clean();
        }
        ob_start();
        $this->outputBufferingActive = true;
        $this->outputBufferingLevel  = ob_get_level();
    }
    private function stopOutputBuffering()
    {
        if (ob_get_level() != $this->outputBufferingLevel) {
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
            throw new PHPUnit_Framework_RiskyTestError(
                'Test code or tested code did not (only) close its own output buffers'
            );
        }
        $output = ob_get_contents();
        if ($this->outputCallback === false) {
            $this->output = $output;
        } else {
            $this->output = call_user_func_array(
                $this->outputCallback,
                array($output)
            );
        }
        ob_end_clean();
        $this->outputBufferingActive = false;
        $this->outputBufferingLevel  = ob_get_level();
    }
    private function snapshotGlobalState()
    {
        $backupGlobals = $this->backupGlobals === null || $this->backupGlobals === true;
        if ($this->runTestInSeparateProcess || $this->inIsolation ||
            (!$backupGlobals && !$this->backupStaticAttributes)) {
            return;
        }
        $this->snapshot = $this->createGlobalStateSnapshot($backupGlobals);
    }
    private function restoreGlobalState()
    {
        if (!$this->snapshot instanceof Snapshot) {
            return;
        }
        $backupGlobals = $this->backupGlobals === null || $this->backupGlobals === true;
        if ($this->disallowChangesToGlobalState) {
            $this->compareGlobalStateSnapshots(
                $this->snapshot,
                $this->createGlobalStateSnapshot($backupGlobals)
            );
        }
        $restorer = new Restorer;
        if ($backupGlobals) {
            $restorer->restoreGlobalVariables($this->snapshot);
        }
        if ($this->backupStaticAttributes) {
            $restorer->restoreStaticAttributes($this->snapshot);
        }
        $this->snapshot = null;
    }
    private function createGlobalStateSnapshot($backupGlobals)
    {
        $blacklist = new Blacklist;
        foreach ($this->backupGlobalsBlacklist as $globalVariable) {
            $blacklist->addGlobalVariable($globalVariable);
        }
        if (!defined('PHPUNIT_TESTSUITE')) {
            $blacklist->addClassNamePrefix('PHPUnit');
            $blacklist->addClassNamePrefix('File_Iterator');
            $blacklist->addClassNamePrefix('PHP_CodeCoverage');
            $blacklist->addClassNamePrefix('PHP_Invoker');
            $blacklist->addClassNamePrefix('PHP_Timer');
            $blacklist->addClassNamePrefix('PHP_Token');
            $blacklist->addClassNamePrefix('Symfony');
            $blacklist->addClassNamePrefix('Text_Template');
            $blacklist->addClassNamePrefix('Doctrine\Instantiator');
            foreach ($this->backupStaticAttributesBlacklist as $class => $attributes) {
                foreach ($attributes as $attribute) {
                    $blacklist->addStaticAttribute($class, $attribute);
                }
            }
        }
        return new Snapshot(
            $blacklist,
            $backupGlobals,
            $this->backupStaticAttributes,
            false,
            false,
            false,
            false,
            false,
            false,
            false
        );
    }
    private function compareGlobalStateSnapshots(Snapshot $before, Snapshot $after)
    {
        $backupGlobals = $this->backupGlobals === null || $this->backupGlobals === true;
        if ($backupGlobals) {
            $this->compareGlobalStateSnapshotPart(
                $before->globalVariables(),
                $after->globalVariables(),
                "--- Global variables before the test\n+++ Global variables after the test\n"
            );
            $this->compareGlobalStateSnapshotPart(
                $before->superGlobalVariables(),
                $after->superGlobalVariables(),
                "--- Super-global variables before the test\n+++ Super-global variables after the test\n"
            );
        }
        if ($this->backupStaticAttributes) {
            $this->compareGlobalStateSnapshotPart(
                $before->staticAttributes(),
                $after->staticAttributes(),
                "--- Static attributes before the test\n+++ Static attributes after the test\n"
            );
        }
    }
    private function compareGlobalStateSnapshotPart(array $before, array $after, $header)
    {
        if ($before != $after) {
            $differ   = new Differ($header);
            $exporter = new Exporter;
            $diff = $differ->diff(
                $exporter->export($before),
                $exporter->export($after)
            );
            throw new PHPUnit_Framework_RiskyTestError(
                $diff
            );
        }
    }
    private function getProphet()
    {
        if ($this->prophet === null) {
            $this->prophet = new Prophet;
        }
        return $this->prophet;
    }
}
