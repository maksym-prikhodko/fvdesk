<?php
namespace Psy\Test;
use Psy\CodeCleaner;
class CodeCleanerTest extends \PHPUnit_Framework_TestCase
{
    public function testAutomaticSemicolons(array $lines, $requireSemicolons, $expected)
    {
        $cc = new CodeCleaner();
        $this->assertEquals($expected, $cc->clean($lines, $requireSemicolons));
    }
    public function codeProvider()
    {
        return array(
            array(array('true'),  false, 'return true;'),
            array(array('true;'), false, 'return true;'),
            array(array('true;'), true,  'return true;'),
            array(array('true'),  true,  false),
            array(array('echo "foo";', 'true'), false, "echo 'foo';\nreturn true;"),
            array(array('echo "foo";', 'true'), true , false),
        );
    }
}
