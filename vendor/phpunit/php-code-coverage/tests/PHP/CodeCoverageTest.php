<?php
if (!defined('TEST_FILES_PATH')) {
    define(
      'TEST_FILES_PATH',
      dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR .
      '_files' . DIRECTORY_SEPARATOR
    );
}
require_once TEST_FILES_PATH . '../TestCase.php';
require_once TEST_FILES_PATH . 'BankAccount.php';
require_once TEST_FILES_PATH . 'BankAccountTest.php';
class PHP_CodeCoverageTest extends PHP_CodeCoverage_TestCase
{
    private $coverage;
    protected function setUp()
    {
        $this->coverage = new PHP_CodeCoverage;
    }
    public function testConstructor()
    {
        $this->assertAttributeInstanceOf(
          'PHP_CodeCoverage_Driver_Xdebug', 'driver', $this->coverage
        );
        $this->assertAttributeInstanceOf(
          'PHP_CodeCoverage_Filter', 'filter', $this->coverage
        );
    }
    public function testConstructor2()
    {
        $filter   = new PHP_CodeCoverage_Filter;
        $coverage = new PHP_CodeCoverage(null, $filter);
        $this->assertAttributeInstanceOf(
          'PHP_CodeCoverage_Driver_Xdebug', 'driver', $coverage
        );
        $this->assertSame($filter, $coverage->filter());
    }
    public function testStartThrowsExceptionForInvalidArgument()
    {
        $this->coverage->start(null, array(), null);
    }
    public function testStopThrowsExceptionForInvalidArgument()
    {
        $this->coverage->stop(null);
    }
    public function testStopThrowsExceptionForInvalidArgument2()
    {
        $this->coverage->stop(true, null);
    }
    public function testAppendThrowsExceptionForInvalidArgument()
    {
        $this->coverage->append(array(), null);
    }
    public function testSetCacheTokensThrowsExceptionForInvalidArgument()
    {
        $this->coverage->setCacheTokens(null);
    }
    public function testSetCacheTokens()
    {
        $this->coverage->setCacheTokens(true);
        $this->assertAttributeEquals(true, 'cacheTokens', $this->coverage);
    }
    public function testSetCheckForUnintentionallyCoveredCodeThrowsExceptionForInvalidArgument()
    {
        $this->coverage->setCheckForUnintentionallyCoveredCode(null);
    }
    public function testSetCheckForUnintentionallyCoveredCode()
    {
        $this->coverage->setCheckForUnintentionallyCoveredCode(true);
        $this->assertAttributeEquals(
          true, 'checkForUnintentionallyCoveredCode', $this->coverage
        );
    }
    public function testSetForceCoversAnnotationThrowsExceptionForInvalidArgument()
    {
        $this->coverage->setForceCoversAnnotation(null);
    }
    public function testSetForceCoversAnnotation()
    {
        $this->coverage->setForceCoversAnnotation(true);
        $this->assertAttributeEquals(
          true, 'forceCoversAnnotation', $this->coverage
        );
    }
    public function testSetAddUncoveredFilesFromWhitelistThrowsExceptionForInvalidArgument()
    {
        $this->coverage->setAddUncoveredFilesFromWhitelist(null);
    }
    public function testSetAddUncoveredFilesFromWhitelist()
    {
        $this->coverage->setAddUncoveredFilesFromWhitelist(true);
        $this->assertAttributeEquals(
          true, 'addUncoveredFilesFromWhitelist', $this->coverage
        );
    }
    public function testSetProcessUncoveredFilesFromWhitelistThrowsExceptionForInvalidArgument()
    {
        $this->coverage->setProcessUncoveredFilesFromWhitelist(null);
    }
    public function testSetProcessUncoveredFilesFromWhitelist()
    {
        $this->coverage->setProcessUncoveredFilesFromWhitelist(true);
        $this->assertAttributeEquals(
          true, 'processUncoveredFilesFromWhitelist', $this->coverage
        );
    }
    public function testSetMapTestClassNameToCoveredClassName()
    {
        $this->coverage->setMapTestClassNameToCoveredClassName(true);
        $this->assertAttributeEquals(
          true, 'mapTestClassNameToCoveredClassName', $this->coverage
        );
    }
    public function testSetMapTestClassNameToCoveredClassNameThrowsExceptionForInvalidArgument()
    {
        $this->coverage->setMapTestClassNameToCoveredClassName(null);
    }
    public function testClear()
    {
        $this->coverage->clear();
        $this->assertAttributeEquals(null, 'currentId', $this->coverage);
        $this->assertAttributeEquals(array(), 'data', $this->coverage);
        $this->assertAttributeEquals(array(), 'tests', $this->coverage);
    }
    public function testCollect()
    {
        $coverage = $this->getCoverageForBankAccount();
        $this->assertEquals(
          $this->getExpectedDataArrayForBankAccount(), $coverage->getData()
        );
        $this->assertEquals(
          array(
            'BankAccountTest::testBalanceIsInitiallyZero' => null,
            'BankAccountTest::testBalanceCannotBecomeNegative' => null,
            'BankAccountTest::testBalanceCannotBecomeNegative2' => null,
            'BankAccountTest::testDepositWithdrawMoney' => null
          ),
          $coverage->getTests()
        );
    }
    public function testMerge()
    {
        $coverage = $this->getCoverageForBankAccountForFirstTwoTests();
        $coverage->merge($this->getCoverageForBankAccountForLastTwoTests());
        $this->assertEquals(
          $this->getExpectedDataArrayForBankAccount(), $coverage->getData()
        );
    }
    public function testMerge2()
    {
        $coverage = new PHP_CodeCoverage(
          $this->getMock('PHP_CodeCoverage_Driver_Xdebug'),
          new PHP_CodeCoverage_Filter
        );
        $coverage->merge($this->getCoverageForBankAccount());
        $this->assertEquals(
          $this->getExpectedDataArrayForBankAccount(), $coverage->getData()
        );
    }
    public function testGetLinesToBeIgnored()
    {
        $this->assertEquals(
          array(
             1,
             3,
             4,
             5,
             7,
             8,
             9,
            10,
            11,
            12,
            13,
            14,
            15,
            16,
            17,
            18,
            19,
            20,
            21,
            22,
            23,
            24,
            25,
            26,
            27,
            28,
            30,
            32,
            33,
            34,
            35,
            36,
            37,
            38
          ),
          $this->getLinesToBeIgnored()->invoke(
            $this->coverage,
            TEST_FILES_PATH . 'source_with_ignore.php'
          )
        );
    }
    public function testGetLinesToBeIgnored2()
    {
        $this->assertEquals(
          array(1, 5),
          $this->getLinesToBeIgnored()->invoke(
            $this->coverage,
            TEST_FILES_PATH . 'source_without_ignore.php'
          )
        );
    }
    public function testGetLinesToBeIgnored3()
    {
        $this->assertEquals(
          array(
            1,
            2,
            3,
            4,
            5,
            8,
            11,
            15,
            16,
            19,
            20
          ),
          $this->getLinesToBeIgnored()->invoke(
            $this->coverage,
            TEST_FILES_PATH . 'source_with_class_and_anonymous_function.php'
          )
        );
    }
    public function testGetLinesToBeIgnoredOneLineAnnotations()
    {
        $this->assertEquals(
          array(
            1,
            2,
            3,
            4,
            5,
            6,
            7,
            8,
            9,
            10,
            11,
            12,
            13,
            14,
            15,
            16,
            18,
            20,
            21,
            23,
            24,
            25,
            27,
            28,
            29,
            30,
            31,
            32,
            33,
            34,
            37
          ),
          $this->getLinesToBeIgnored()->invoke(
            $this->coverage,
            TEST_FILES_PATH . 'source_with_oneline_annotations.php'
          )
        );
    }
    private function getLinesToBeIgnored()
    {
        $getLinesToBeIgnored = new ReflectionMethod(
            'PHP_CodeCoverage', 'getLinesToBeIgnored'
        );
        $getLinesToBeIgnored->setAccessible(true);
        return $getLinesToBeIgnored;
    }
}
