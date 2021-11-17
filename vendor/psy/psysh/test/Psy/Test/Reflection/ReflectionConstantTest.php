<?php
namespace Psy\Test\Reflection;
use Psy\Reflection\ReflectionConstant;
class ReflectionConstantTest extends \PHPUnit_Framework_TestCase
{
    const CONSTANT_ONE = 'one';
    public function testConstruction()
    {
        $refl  = new ReflectionConstant($this, 'CONSTANT_ONE');
        $class = $refl->getDeclaringClass();
        $this->assertTrue($class instanceof \ReflectionClass);
        $this->assertEquals('Psy\Test\Reflection\ReflectionConstantTest', $class->getName());
        $this->assertEquals('CONSTANT_ONE', $refl->getName());
        $this->assertEquals('CONSTANT_ONE', (string) $refl);
        $this->assertEquals('one', $refl->getValue());
        $this->assertEquals(null, $refl->getFileName());
        $this->assertFalse($refl->getDocComment());
    }
    public function testUnknownConstantThrowsException()
    {
        new ReflectionConstant($this, 'UNKNOWN_CONSTANT');
    }
    public function testNotYetImplemented($method)
    {
        $refl = new ReflectionConstant($this, 'CONSTANT_ONE');
        $refl->$method();
    }
    public function notYetImplemented()
    {
        return array(
            array('getStartLine'),
            array('getEndLine'),
            array('export'),
        );
    }
}
