<?php
namespace SebastianBergmann\RecursionContext;
use PHPUnit_Framework_TestCase;
class ContextTest extends PHPUnit_Framework_TestCase
{
    private $context;
    protected function setUp()
    {
        $this->context = new Context();
    }
    public function failsProvider()
    {
        return array(
            array(true),
            array(false),
            array(null),
            array('string'),
            array(1),
            array(1.5),
            array(fopen('php:
        );
    }
    public function valuesProvider()
    {
        $obj2 = new \stdClass();
        $obj2->foo = 'bar';
        $obj3 = (object) array(1,2,"Test\r\n",4,5,6,7,8);
        $obj = new \stdClass();
        $obj->null = null;
        $obj->boolean = true;
        $obj->integer = 1;
        $obj->double = 1.2;
        $obj->string = '1';
        $obj->text = "this\nis\na\nvery\nvery\nvery\nvery\nvery\nvery\rlong\n\rtext";
        $obj->object = $obj2;
        $obj->objectagain = $obj2;
        $obj->array = array('foo' => 'bar');
        $obj->array2 = array(1,2,3,4,5,6);
        $obj->array3 = array($obj, $obj2, $obj3);
        $obj->self = $obj;
        $storage = new \SplObjectStorage();
        $storage->attach($obj2);
        $storage->foo = $obj2;
        return array(
            array($obj, spl_object_hash($obj)),
            array($obj2, spl_object_hash($obj2)),
            array($obj3, spl_object_hash($obj3)),
            array($storage, spl_object_hash($storage)),
            array($obj->array, 0),
            array($obj->array2, 0),
            array($obj->array3, 0)
        );
    }
    public function testAddFails($value)
    {
        $this->setExpectedException(
          'SebastianBergmann\\RecursionContext\\Exception',
          'Only arrays and objects are supported'
        );
        $this->context->add($value);
    }
    public function testContainsFails($value)
    {
        $this->setExpectedException(
          'SebastianBergmann\\RecursionContext\\Exception',
          'Only arrays and objects are supported'
        );
        $this->context->contains($value);
    }
    public function testAdd($value, $key)
    {
        $this->assertEquals($key, $this->context->add($value));
        $this->assertEquals($key, $this->context->add($value));
    }
    public function testContainsFound($value, $key)
    {
        $this->context->add($value);
        $this->assertEquals($key, $this->context->contains($value));
        $this->assertEquals($key, $this->context->contains($value));
    }
    public function testContainsNotFound($value)
    {
        $this->assertFalse($this->context->contains($value));
    }
}
