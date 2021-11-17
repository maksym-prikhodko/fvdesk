<?php
namespace spec\Prophecy\Exception\Prediction;
use PhpSpec\ObjectBehavior;
class NoCallsExceptionSpec extends ObjectBehavior
{
    function let($objectProphecy, $methodProphecy)
    {
        $methodProphecy->getObjectProphecy()->willReturn($objectProphecy);
        $this->beConstructedWith('message', $methodProphecy);
    }
    function it_is_PredictionException()
    {
        $this->shouldHaveType('Prophecy\Exception\Prediction\PredictionException');
    }
    function it_extends_MethodProphecyException()
    {
        $this->shouldHaveType('Prophecy\Exception\Prophecy\MethodProphecyException');
    }
}
