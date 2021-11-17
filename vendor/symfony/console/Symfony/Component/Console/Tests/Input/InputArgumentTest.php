<?php
namespace Symfony\Component\Console\Tests\Input;
use Symfony\Component\Console\Input\InputArgument;
class InputArgumentTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $argument = new InputArgument('foo');
        $this->assertEquals('foo', $argument->getName(), '__construct() takes a name as its first argument');
    }
    public function testModes()
    {
        $argument = new InputArgument('foo');
        $this->assertFalse($argument->isRequired(), '__construct() gives a "InputArgument::OPTIONAL" mode by default');
        $argument = new InputArgument('foo', null);
        $this->assertFalse($argument->isRequired(), '__construct() can take "InputArgument::OPTIONAL" as its mode');
        $argument = new InputArgument('foo', InputArgument::OPTIONAL);
        $this->assertFalse($argument->isRequired(), '__construct() can take "InputArgument::OPTIONAL" as its mode');
        $argument = new InputArgument('foo', InputArgument::REQUIRED);
        $this->assertTrue($argument->isRequired(), '__construct() can take "InputArgument::REQUIRED" as its mode');
    }
    public function testInvalidModes($mode)
    {
        $this->setExpectedException('InvalidArgumentException', sprintf('Argument mode "%s" is not valid.', $mode));
        new InputArgument('foo', $mode);
    }
    public function provideInvalidModes()
    {
        return array(
            array('ANOTHER_ONE'),
            array(-1),
        );
    }
    public function testIsArray()
    {
        $argument = new InputArgument('foo', InputArgument::IS_ARRAY);
        $this->assertTrue($argument->isArray(), '->isArray() returns true if the argument can be an array');
        $argument = new InputArgument('foo', InputArgument::OPTIONAL | InputArgument::IS_ARRAY);
        $this->assertTrue($argument->isArray(), '->isArray() returns true if the argument can be an array');
        $argument = new InputArgument('foo', InputArgument::OPTIONAL);
        $this->assertFalse($argument->isArray(), '->isArray() returns false if the argument can not be an array');
    }
    public function testGetDescription()
    {
        $argument = new InputArgument('foo', null, 'Some description');
        $this->assertEquals('Some description', $argument->getDescription(), '->getDescription() return the message description');
    }
    public function testGetDefault()
    {
        $argument = new InputArgument('foo', InputArgument::OPTIONAL, '', 'default');
        $this->assertEquals('default', $argument->getDefault(), '->getDefault() return the default value');
    }
    public function testSetDefault()
    {
        $argument = new InputArgument('foo', InputArgument::OPTIONAL, '', 'default');
        $argument->setDefault(null);
        $this->assertNull($argument->getDefault(), '->setDefault() can reset the default value by passing null');
        $argument->setDefault('another');
        $this->assertEquals('another', $argument->getDefault(), '->setDefault() changes the default value');
        $argument = new InputArgument('foo', InputArgument::OPTIONAL | InputArgument::IS_ARRAY);
        $argument->setDefault(array(1, 2));
        $this->assertEquals(array(1, 2), $argument->getDefault(), '->setDefault() changes the default value');
    }
    public function testSetDefaultWithRequiredArgument()
    {
        $argument = new InputArgument('foo', InputArgument::REQUIRED);
        $argument->setDefault('default');
    }
    public function testSetDefaultWithArrayArgument()
    {
        $argument = new InputArgument('foo', InputArgument::IS_ARRAY);
        $argument->setDefault('default');
    }
}