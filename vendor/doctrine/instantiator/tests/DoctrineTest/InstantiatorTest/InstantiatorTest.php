<?php
namespace DoctrineTest\InstantiatorTest;
use Doctrine\Instantiator\Exception\UnexpectedValueException;
use Doctrine\Instantiator\Instantiator;
use PHPUnit_Framework_TestCase;
use ReflectionClass;
class InstantiatorTest extends PHPUnit_Framework_TestCase
{
    private $instantiator;
    protected function setUp()
    {
        $this->instantiator = new Instantiator();
    }
    public function testCanInstantiate($className)
    {
        $this->assertInstanceOf($className, $this->instantiator->instantiate($className));
    }
    public function testInstantiatesSeparateInstances($className)
    {
        $instance1 = $this->instantiator->instantiate($className);
        $instance2 = $this->instantiator->instantiate($className);
        $this->assertEquals($instance1, $instance2);
        $this->assertNotSame($instance1, $instance2);
    }
    public function testExceptionOnUnSerializationException()
    {
        if (defined('HHVM_VERSION')) {
            $this->markTestSkipped(
                'As of facebook/hhvm#3432, HHVM has no PDORow, and therefore '
                . ' no internal final classes that cannot be instantiated'
            );
        }
        $className = 'DoctrineTest\\InstantiatorTestAsset\\UnserializeExceptionArrayObjectAsset';
        if (\PHP_VERSION_ID >= 50600) {
            $className = 'PDORow';
        }
        if (\PHP_VERSION_ID === 50429 || \PHP_VERSION_ID === 50513) {
            $className = 'DoctrineTest\\InstantiatorTestAsset\\SerializableArrayObjectAsset';
        }
        $this->setExpectedException('Doctrine\\Instantiator\\Exception\\UnexpectedValueException');
        $this->instantiator->instantiate($className);
    }
    public function testNoticeOnUnSerializationException()
    {
        if (\PHP_VERSION_ID >= 50600) {
            $this->markTestSkipped(
                'PHP 5.6 supports `ReflectionClass#newInstanceWithoutConstructor()` for some internal classes'
            );
        }
        try {
            $this->instantiator->instantiate('DoctrineTest\\InstantiatorTestAsset\\WakeUpNoticesAsset');
            $this->fail('No exception was raised');
        } catch (UnexpectedValueException $exception) {
            $wakeUpNoticesReflection = new ReflectionClass('DoctrineTest\\InstantiatorTestAsset\\WakeUpNoticesAsset');
            $previous                = $exception->getPrevious();
            $this->assertInstanceOf('Exception', $previous);
            if (! (\PHP_VERSION_ID === 50429 || \PHP_VERSION_ID === 50513)) {
                $this->assertSame(
                    'Could not produce an instance of "DoctrineTest\\InstantiatorTestAsset\WakeUpNoticesAsset" '
                    . 'via un-serialization, since an error was triggered in file "'
                    . $wakeUpNoticesReflection->getFileName() . '" at line "36"',
                    $exception->getMessage()
                );
                $this->assertSame('Something went bananas while un-serializing this instance', $previous->getMessage());
                $this->assertSame(\E_USER_NOTICE, $previous->getCode());
            }
        }
    }
    public function testInstantiationFromNonExistingClass($invalidClassName)
    {
        $this->setExpectedException('Doctrine\\Instantiator\\Exception\\InvalidArgumentException');
        $this->instantiator->instantiate($invalidClassName);
    }
    public function testInstancesAreNotCloned()
    {
        $className = 'TemporaryClass' . uniqid();
        eval('namespace ' . __NAMESPACE__ . '; class ' . $className . '{}');
        $instance = $this->instantiator->instantiate(__NAMESPACE__ . '\\' . $className);
        $instance->foo = 'bar';
        $instance2 = $this->instantiator->instantiate(__NAMESPACE__ . '\\' . $className);
        $this->assertObjectNotHasAttribute('foo', $instance2);
    }
    public function getInstantiableClasses()
    {
        $classes = array(
            array('stdClass'),
            array(__CLASS__),
            array('Doctrine\\Instantiator\\Instantiator'),
            array('PharException'),
            array('DoctrineTest\\InstantiatorTestAsset\\SimpleSerializableAsset'),
            array('DoctrineTest\\InstantiatorTestAsset\\PharExceptionAsset'),
            array('DoctrineTest\\InstantiatorTestAsset\\UnCloneableAsset'),
            array('DoctrineTest\\InstantiatorTestAsset\\XMLReaderAsset'),
        );
        if (\PHP_VERSION_ID === 50429 || \PHP_VERSION_ID === 50513) {
            return $classes;
        }
        $classes = array_merge(
            $classes,
            array(
                array('PharException'),
                array('ArrayObject'),
                array('DoctrineTest\\InstantiatorTestAsset\\ArrayObjectAsset'),
                array('DoctrineTest\\InstantiatorTestAsset\\SerializableArrayObjectAsset'),
            )
        );
        if (\PHP_VERSION_ID >= 50600) {
            $classes[] = array('DoctrineTest\\InstantiatorTestAsset\\WakeUpNoticesAsset');
            $classes[] = array('DoctrineTest\\InstantiatorTestAsset\\UnserializeExceptionArrayObjectAsset');
        }
        return $classes;
    }
    public function getInvalidClassNames()
    {
        $classNames = array(
            array(__CLASS__ . uniqid()),
            array('Doctrine\\Instantiator\\InstantiatorInterface'),
            array('DoctrineTest\\InstantiatorTestAsset\\AbstractClassAsset'),
        );
        if (\PHP_VERSION_ID >= 50400) {
            $classNames[] = array('DoctrineTest\\InstantiatorTestAsset\\SimpleTraitAsset');
        }
        return $classNames;
    }
}