<?php
namespace spec\Prophecy\Exception\Prediction;
use PhpSpec\ObjectBehavior;
class UnexpectedCallsExceptionSpec extends ObjectBehavior
{
    function let($objectProphecy, $methodProphecy, $call1, $call2)
    {
        $methodProphecy->getObjectProphecy()->willReturn($objectProphecy);
        $this->beConstructedWith('message', $methodProphecy, array($call1, $call2));
    }
    function it_is_PredictionException()
    {
        $this->shouldHaveType('Prophecy\Exception\Prediction\PredictionException');
    }
    function it_extends_MethodProphecyException()
    {
        $this->shouldHaveType('Prophecy\Exception\Prophecy\MethodProphecyException');
    }
    function it_should_expose_calls_list_through_getter($call1, $call2)
    {
        $this->getCalls()->shouldReturn(array($call1, $call2));
    }
}
