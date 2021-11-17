<?php
if (!defined('TEST_FILES_PATH')) {
    define(
      'TEST_FILES_PATH',
      dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR .
      '_files' . DIRECTORY_SEPARATOR
    );
}
require_once TEST_FILES_PATH . '../TestCase.php';
class PHP_CodeCoverage_Report_CloverTest extends PHP_CodeCoverage_TestCase
{
    public function testCloverForBankAccountTest()
    {
        $clover = new PHP_CodeCoverage_Report_Clover;
        $this->assertStringMatchesFormatFile(
          TEST_FILES_PATH . 'BankAccount-clover.xml',
          $clover->process($this->getCoverageForBankAccount(), null, 'BankAccount')
        );
    }
    public function testCloverForFileWithIgnoredLines()
    {
        $clover = new PHP_CodeCoverage_Report_Clover;
        $this->assertStringMatchesFormatFile(
          TEST_FILES_PATH . 'ignored-lines-clover.xml',
          $clover->process($this->getCoverageForFileWithIgnoredLines())
        );
    }
    public function testCloverForClassWithAnonymousFunction()
    {
        $clover = new PHP_CodeCoverage_Report_Clover;
        $this->assertStringMatchesFormatFile(
          TEST_FILES_PATH . 'class-with-anonymous-function-clover.xml',
          $clover->process($this->getCoverageForClassWithAnonymousFunction())
        );
    }
}
