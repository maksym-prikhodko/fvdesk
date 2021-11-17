<?php
namespace Symfony\Component\HttpKernel\Tests\EventListener;
use Symfony\Component\HttpKernel\EventListener\AddRequestFormatsListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelEvents;
class AddRequestFormatsListenerTest extends \PHPUnit_Framework_TestCase
{
    private $listener;
    protected function setUp()
    {
        $this->listener = new AddRequestFormatsListener(array('csv' => array('text/csv', 'text/plain')));
    }
    protected function tearDown()
    {
        $this->listener = null;
    }
    public function testIsAnEventSubscriber()
    {
        $this->assertInstanceOf('Symfony\Component\EventDispatcher\EventSubscriberInterface', $this->listener);
    }
    public function testRegisteredEvent()
    {
        $this->assertEquals(
            array(KernelEvents::REQUEST => 'onKernelRequest'),
            AddRequestFormatsListener::getSubscribedEvents()
        );
    }
    public function testSetAdditionalFormats()
    {
        $request = $this->getRequestMock();
        $event = $this->getGetResponseEventMock($request);
        $request->expects($this->once())
            ->method('setFormat')
            ->with('csv', array('text/csv', 'text/plain'));
        $this->listener->onKernelRequest($event);
    }
    protected function getRequestMock()
    {
        return $this->getMock('Symfony\Component\HttpFoundation\Request');
    }
    protected function getGetResponseEventMock(Request $request)
    {
        $event = $this
            ->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($request));
        return $event;
    }
}
