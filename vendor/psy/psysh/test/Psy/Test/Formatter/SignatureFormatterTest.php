<?php
namespace Psy\Test\Formatter;
use Psy\Formatter\SignatureFormatter;
use Psy\Reflection\ReflectionConstant;
class SignatureFormatterTest extends \PHPUnit_Framework_TestCase
{
    const FOO = 'foo value';
    private static $bar = 'bar value';
    private function someFakeMethod(array $one, $two = 'TWO', \Reflector $three = null)
    {
    }
    public function testFormat($reflector, $expected)
    {
        $this->assertEquals($expected, strip_tags(SignatureFormatter::format($reflector)));
    }
    public function signatureReflectors()
    {
        return array(
            array(
                new \ReflectionClass($this),
                "class Psy\Test\Formatter\SignatureFormatterTest "
                . "extends PHPUnit_Framework_TestCase implements "
                . "PHPUnit_Framework_SelfDescribing, Countable, "
                . "PHPUnit_Framework_Test",
            ),
            array(
                new \ReflectionFunction('implode'),
                'function implode($glue, $pieces)',
            ),
            array(
                new ReflectionConstant($this, 'FOO'),
                'const FOO = "foo value"',
            ),
            array(
                new \ReflectionMethod($this, 'someFakeMethod'),
                'private function someFakeMethod(array $one, $two = \'TWO\', Reflector $three = null)',
            ),
            array(
                new \ReflectionProperty($this, 'bar'),
                'private static $bar',
            ),
            array(
                new \ReflectionClass('Psy\CodeCleaner\CodeCleanerPass'),
                'abstract class Psy\CodeCleaner\CodeCleanerPass extends PhpParser\NodeVisitorAbstract implements PhpParser\NodeVisitor',
            ),
        );
    }
    public function testSignatureFormatterThrowsUnknownReflectorExpeption()
    {
        $refl = $this->getMock('Reflector');
        SignatureFormatter::format($refl);
    }
}
