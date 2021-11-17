<?php
namespace DoctrineTest\InstantiatorTest\Exception;
use Doctrine\Instantiator\Exception\UnexpectedValueException;
use Exception;
use PHPUnit_Framework_TestCase;
use ReflectionClass;
class UnexpectedValueExceptionTest extends PHPUnit_Framework_TestCase
{
    public function testFromSerializationTriggeredException()
    {
        $reflectionClass = new ReflectionClass($this);
        $previous        = new Exception();
        $exception       = UnexpectedValueException::fromSerializationTriggeredException($reflectionClass, $previous);
        $this->assertInstanceOf('Doctrine\\Instantiator\\Exception\\UnexpectedValueException', $exception);
        $this->assertSame($previous, $exception->getPrevious());
        $this->assertSame(
            'An exception was raised while trying to instantiate an instance of "'
            . __CLASS__  . '" via un-serialization',
            $exception->getMessage()
        );
    }
    public function testFromUncleanUnSerialization()
    {
        $reflection = new ReflectionClass('DoctrineTest\\InstantiatorTestAsset\\AbstractClassAsset');
        $exception  = UnexpectedValueException::fromUncleanUnSerialization($reflection, 'foo', 123, 'bar', 456);
        $this->assertInstanceOf('Doctrine\\Instantiator\\Exception\\UnexpectedValueException', $exception);
        $this->assertSame(
            'Could not produce an instance of "DoctrineTest\\InstantiatorTestAsset\\AbstractClassAsset" '
            . 'via un-serialization, since an error was triggered in file "bar" at line "456"',
            $exception->getMessage()
        );
        $previous = $exception->getPrevious();
        $this->assertInstanceOf('Exception', $previous);
        $this->assertSame('foo', $previous->getMessage());
        $this->assertSame(123, $previous->getCode());
    }
}
