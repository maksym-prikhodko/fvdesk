<?php
namespace DoctrineTest\InstantiatorTest\Exception;
use Doctrine\Instantiator\Exception\InvalidArgumentException;
use PHPUnit_Framework_TestCase;
use ReflectionClass;
class InvalidArgumentExceptionTest extends PHPUnit_Framework_TestCase
{
    public function testFromNonExistingTypeWithNonExistingClass()
    {
        $className = __CLASS__ . uniqid();
        $exception = InvalidArgumentException::fromNonExistingClass($className);
        $this->assertInstanceOf('Doctrine\\Instantiator\\Exception\\InvalidArgumentException', $exception);
        $this->assertSame('The provided class "' . $className . '" does not exist', $exception->getMessage());
    }
    public function testFromNonExistingTypeWithTrait()
    {
        if (PHP_VERSION_ID < 50400) {
            $this->markTestSkipped('Need at least PHP 5.4.0, as this test requires traits support to run');
        }
        $exception = InvalidArgumentException::fromNonExistingClass(
            'DoctrineTest\\InstantiatorTestAsset\\SimpleTraitAsset'
        );
        $this->assertSame(
            'The provided type "DoctrineTest\\InstantiatorTestAsset\\SimpleTraitAsset" is a trait, '
            . 'and can not be instantiated',
            $exception->getMessage()
        );
    }
    public function testFromNonExistingTypeWithInterface()
    {
        $exception = InvalidArgumentException::fromNonExistingClass('Doctrine\\Instantiator\\InstantiatorInterface');
        $this->assertSame(
            'The provided type "Doctrine\\Instantiator\\InstantiatorInterface" is an interface, '
            . 'and can not be instantiated',
            $exception->getMessage()
        );
    }
    public function testFromAbstractClass()
    {
        $reflection = new ReflectionClass('DoctrineTest\\InstantiatorTestAsset\\AbstractClassAsset');
        $exception  = InvalidArgumentException::fromAbstractClass($reflection);
        $this->assertSame(
            'The provided class "DoctrineTest\\InstantiatorTestAsset\\AbstractClassAsset" is abstract, '
            . 'and can not be instantiated',
            $exception->getMessage()
        );
    }
}
