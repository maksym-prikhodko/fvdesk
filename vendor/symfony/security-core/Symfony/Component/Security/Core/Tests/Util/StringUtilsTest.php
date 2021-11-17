<?php
namespace Symfony\Component\Security\Core\Tests\Util;
use Symfony\Component\Security\Core\Util\StringUtils;
class StringUtilsTest extends \PHPUnit_Framework_TestCase
{
    public function dataProviderTrue()
    {
        return array(
            array('same', 'same'),
            array('', ''),
            array(123, 123),
            array(null, ''),
            array(null, null),
        );
    }
    public function dataProviderFalse()
    {
        return array(
            array('not1same', 'not2same'),
            array('short', 'longer'),
            array('longer', 'short'),
            array('', 'notempty'),
            array('notempty', ''),
            array(123, 'NaN'),
            array('NaN', 123),
            array(null, 123),
        );
    }
    public function testEqualsTrue($known, $user)
    {
        $this->assertTrue(StringUtils::equals($known, $user));
    }
    public function testEqualsFalse($known, $user)
    {
        $this->assertFalse(StringUtils::equals($known, $user));
    }
}
