<?php
namespace Symfony\Component\Finder\Tests\Expression;
use Symfony\Component\Finder\Expression\Expression;
class ExpressionTest extends \PHPUnit_Framework_TestCase
{
    public function testTypeGuesser($expr, $type)
    {
        $this->assertEquals($type, Expression::create($expr)->getType());
    }
    public function testCaseSensitive($expr, $isCaseSensitive)
    {
        $this->assertEquals($isCaseSensitive, Expression::create($expr)->isCaseSensitive());
    }
    public function testRegexRendering($expr, $body)
    {
        $this->assertEquals($body, Expression::create($expr)->renderPattern());
    }
    public function getTypeGuesserData()
    {
        return array(
            array('{foo}', Expression::TYPE_REGEX),
            array('/foo/', Expression::TYPE_REGEX),
            array('foo',   Expression::TYPE_GLOB),
            array('foo*',  Expression::TYPE_GLOB),
        );
    }
    public function getCaseSensitiveData()
    {
        return array(
            array('{foo}m', true),
            array('/foo/i', false),
            array('foo*',   true),
        );
    }
    public function getRegexRenderingData()
    {
        return array(
            array('{foo}m', 'foo'),
            array('/foo/i', 'foo'),
        );
    }
}
