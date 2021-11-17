<?php
class Util_RegexTest extends PHPUnit_Framework_TestCase
{
    public function validRegexpProvider()
    {
        return array(
          array('#valid regexp#', 'valid regexp', 1),
          array(';val.*xp;', 'valid regexp', 1),
          array('/val.*xp/i', 'VALID REGEXP', 1),
          array('/a val.*p/','valid regexp', 0),
        );
    }
    public function invalidRegexpProvider()
    {
        return array(
          array('valid regexp', 'valid regexp'),
          array(';val.*xp', 'valid regexp'),
          array('val.*xp/i', 'VALID REGEXP'),
        );
    }
    public function testValidRegex($pattern, $subject, $return)
    {
        $this->assertEquals($return, PHPUnit_Util_Regex::pregMatchSafe($pattern, $subject));
    }
    public function testInvalidRegex($pattern, $subject)
    {
        $this->assertFalse(PHPUnit_Util_Regex::pregMatchSafe($pattern, $subject));
    }
}
