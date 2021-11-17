<?php
namespace spec\Prophecy\Promise;
use PhpSpec\ObjectBehavior;
class CallbackPromiseSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('get_class');
    }
    function it_is_promise()
    {
        $this->shouldBeAnInstanceOf('Prophecy\Promise\PromiseInterface');
    }
    function it_should_execute_closure_callback($object, $method)
    {
        $firstArgumentCallback = function ($args) {
            return $args[0];
        };
        $this->beConstructedWith($firstArgumentCallback);
        $this->execute(array('one', 'two'), $object, $method)->shouldReturn('one');
    }
    function it_should_execute_static_array_callback($object, $method)
    {
        $firstArgumentCallback = array('spec\Prophecy\Promise\ClassCallback', 'staticCallbackMethod');
        $this->beConstructedWith($firstArgumentCallback);
        $this->execute(array('one', 'two'), $object, $method)->shouldReturn('one');
    }
    function it_should_execute_instance_array_callback($object, $method)
    {
        $class = new ClassCallback();
        $firstArgumentCallback = array($class, 'callbackMethod');
        $this->beConstructedWith($firstArgumentCallback);
        $this->execute(array('one', 'two'), $object, $method)->shouldReturn('one');
    }
    function it_should_execute_string_function_callback($object, $method)
    {
        $firstArgumentCallback = 'spec\Prophecy\Promise\functionCallbackFirstArgument';
        $this->beConstructedWith($firstArgumentCallback);
        $this->execute(array('one', 'two'), $object, $method)->shouldReturn('one');
    }
}
class ClassCallback
{
    function callbackMethod($args)
    {
        return $args[0];
    }
    static function staticCallbackMethod($args)
    {
        return $args[0];
    }
}
function functionCallbackFirstArgument($args)
{
    return $args[0];
}
