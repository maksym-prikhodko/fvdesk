<?php
class Framework_MockObject_GeneratorTest extends PHPUnit_Framework_TestCase
{
    protected $generator;
    protected function setUp()
    {
        $this->generator = new PHPUnit_Framework_MockObject_Generator;
    }
    public function testGetMockFailsWhenInvalidFunctionNameIsPassedInAsAFunctionToMock()
    {
        $this->generator->getMock('StdClass', array(0));
    }
    public function testGetMockCanCreateNonExistingFunctions()
    {
        $mock = $this->generator->getMock('StdClass', array('testFunction'));
        $this->assertTrue(method_exists($mock, 'testFunction'));
    }
    public function testGetMockGeneratorFails()
    {
        $mock = $this->generator->getMock('StdClass', array('foo', 'foo'));
    }
    public function testGetMockForAbstractClassDoesNotFailWhenFakingInterfaces()
    {
        $mock = $this->generator->getMockForAbstractClass('Countable');
        $this->assertTrue(method_exists($mock, 'count'));
    }
    public function testGetMockForAbstractClassStubbingAbstractClass()
    {
        $mock = $this->generator->getMockForAbstractClass('AbstractMockTestClass');
        $this->assertTrue(method_exists($mock, 'doSomething'));
    }
    public function testGetMockForAbstractClassWithNonExistentMethods()
    {
        $mock = $this->generator->getMockForAbstractClass(
            'AbstractMockTestClass', array(), '',  true,
            true, true, array('nonexistentMethod')
        );
        $this->assertTrue(method_exists($mock, 'nonexistentMethod'));
        $this->assertTrue(method_exists($mock, 'doSomething'));
    }
    public function testGetMockForAbstractClassShouldCreateStubsOnlyForAbstractMethodWhenNoMethodsWereInformed()
    {
        $mock = $this->generator->getMockForAbstractClass('AbstractMockTestClass');
        $mock->expects($this->any())
             ->method('doSomething')
             ->willReturn('testing');
        $this->assertEquals('testing', $mock->doSomething());
        $this->assertEquals(1, $mock->returnAnything());
    }
    public function testGetMockForAbstractClassExpectingInvalidArgumentException($className, $mockClassName)
    {
        $mock = $this->generator->getMockForAbstractClass($className, array(), $mockClassName);
    }
    public function testGetMockForAbstractClassAbstractClassDoesNotExist()
    {
        $mock = $this->generator->getMockForAbstractClass('Tux');
    }
    public static function getMockForAbstractClassExpectsInvalidArgumentExceptionDataprovider()
    {
        return array(
            'className not a string' => array(array(), ''),
            'mockClassName not a string' => array('Countable', new StdClass),
        );
    }
    public function testGetMockForTraitWithNonExistentMethodsAndNonAbstractMethods()
    {
        $mock = $this->generator->getMockForTrait(
            'AbstractTrait', array(), '',  true,
            true, true, array('nonexistentMethod')
        );
        $this->assertTrue(method_exists($mock, 'nonexistentMethod'));
        $this->assertTrue(method_exists($mock, 'doSomething'));
        $this->assertTrue($mock->mockableMethod());
        $this->assertTrue($mock->anotherMockableMethod());
    }
    public function testGetMockForTraitStubbingAbstractMethod()
    {
        $mock = $this->generator->getMockForTrait('AbstractTrait');
        $this->assertTrue(method_exists($mock, 'doSomething'));
    }
    public function testGetMockForSingletonWithReflectionSuccess()
    {
        require_once __DIR__ . '/_fixture/SingletonClass.php';
        $mock = $this->generator->getMock('SingletonClass', array('doSomething'), array(), '', false);
        $this->assertInstanceOf('SingletonClass', $mock);
    }
    public function testGetMockForSingletonWithUnserializeFail()
    {
        if (version_compare(PHP_VERSION, '5.4.0', '>=')) {
            $this->markTestSkipped('Only for PHP < 5.4.0');
        }
        $this->setExpectedException('PHPUnit_Framework_MockObject_RuntimeException');
        require_once __DIR__ . '/_fixture/SingletonClass.php';
        $mock = $this->generator->getMock('SingletonClass', array('doSomething'), array(), '', false);
    }
}
