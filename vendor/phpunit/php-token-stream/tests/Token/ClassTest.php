<?php
class PHP_Token_ClassTest extends PHPUnit_Framework_TestCase
{
    protected $class;
    protected $function;
    protected function setUp()
    {
        $ts = new PHP_Token_Stream(TEST_FILES_PATH . 'source2.php');
        foreach ($ts as $token) {
            if ($token instanceof PHP_Token_CLASS) {
                $this->class = $token;
            }
            if ($token instanceof PHP_Token_FUNCTION) {
                $this->function = $token;
                break;
            }
        }
    }
    public function testGetClassKeywords()
    {
        $this->assertEquals('abstract', $this->class->getKeywords());
    }
    public function testGetFunctionKeywords()
    {
        $this->assertEquals('abstract,static', $this->function->getKeywords());
    }
    public function testGetFunctionVisibility()
    {
        $this->assertEquals('public', $this->function->getVisibility());
    }
    public function testIssue19()
    {
        $ts = new PHP_Token_Stream(TEST_FILES_PATH . 'issue19.php');
        foreach ($ts as $token) {
            if ($token instanceof PHP_Token_CLASS) {
                $this->assertFalse($token->hasInterfaces());
            }
        }
    }
    public function testIssue30()
    {
        $ts = new PHP_Token_Stream(TEST_FILES_PATH . 'issue30.php');
        $this->assertCount(1, $ts->getClasses());
    }
}
