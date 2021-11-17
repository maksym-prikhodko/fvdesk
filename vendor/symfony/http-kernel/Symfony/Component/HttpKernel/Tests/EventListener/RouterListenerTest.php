<?php
namespace Symfony\Component\HttpKernel\Tests\EventListener;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\EventListener\RouterListener;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Routing\RequestContext;
class RouterListenerTest extends \PHPUnit_Framework_TestCase
{
    private $requestStack;
    protected function setUp()
    {
        $this->requestStack = $this->getMock('Symfony\Component\HttpFoundation\RequestStack', array(), array(), '', false);
    }
    public function testPort($defaultHttpPort, $defaultHttpsPort, $uri, $expectedHttpPort, $expectedHttpsPort)
    {
        $urlMatcher = $this->getMockBuilder('Symfony\Component\Routing\Matcher\UrlMatcherInterface')
                             ->disableOriginalConstructor()
                             ->getMock();
        $context = new RequestContext();
        $context->setHttpPort($defaultHttpPort);
        $context->setHttpsPort($defaultHttpsPort);
        $urlMatcher->expects($this->any())
                     ->method('getContext')
                     ->will($this->returnValue($context));
        $listener = new RouterListener($urlMatcher, null, null, $this->requestStack);
        $event = $this->createGetResponseEventForUri($uri);
        $listener->onKernelRequest($event);
        $this->assertEquals($expectedHttpPort, $context->getHttpPort());
        $this->assertEquals($expectedHttpsPort, $context->getHttpsPort());
        $this->assertEquals(0 === strpos($uri, 'https') ? 'https' : 'http', $context->getScheme());
    }
    public function getPortData()
    {
        return array(
            array(80, 443, 'http:
            array(80, 443, 'http:
            array(80, 443, 'https:
            array(80, 443, 'https:
        );
    }
    private function createGetResponseEventForUri($uri)
    {
        $kernel = $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface');
        $request = Request::create($uri);
        $request->attributes->set('_controller', null); 
        return new GetResponseEvent($kernel, $request, HttpKernelInterface::MASTER_REQUEST);
    }
    public function testInvalidMatcher()
    {
        new RouterListener(new \stdClass(), null, null, $this->requestStack);
    }
    public function testRequestMatcher()
    {
        $kernel = $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface');
        $request = Request::create('http:
        $event = new GetResponseEvent($kernel, $request, HttpKernelInterface::MASTER_REQUEST);
        $requestMatcher = $this->getMock('Symfony\Component\Routing\Matcher\RequestMatcherInterface');
        $requestMatcher->expects($this->once())
                       ->method('matchRequest')
                       ->with($this->isInstanceOf('Symfony\Component\HttpFoundation\Request'))
                       ->will($this->returnValue(array()));
        $listener = new RouterListener($requestMatcher, new RequestContext(), null, $this->requestStack);
        $listener->onKernelRequest($event);
    }
    public function testSubRequestWithDifferentMethod()
    {
        $kernel = $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface');
        $request = Request::create('http:
        $event = new GetResponseEvent($kernel, $request, HttpKernelInterface::MASTER_REQUEST);
        $requestMatcher = $this->getMock('Symfony\Component\Routing\Matcher\RequestMatcherInterface');
        $requestMatcher->expects($this->any())
                       ->method('matchRequest')
                       ->with($this->isInstanceOf('Symfony\Component\HttpFoundation\Request'))
                       ->will($this->returnValue(array()));
        $context = new RequestContext();
        $requestMatcher->expects($this->any())
                       ->method('getContext')
                       ->will($this->returnValue($context));
        $listener = new RouterListener($requestMatcher, new RequestContext(), null, $this->requestStack);
        $listener->onKernelRequest($event);
        $kernel = $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface');
        $request = Request::create('http:
        $event = new GetResponseEvent($kernel, $request, HttpKernelInterface::SUB_REQUEST);
        $listener->onKernelRequest($event);
        $this->assertEquals('GET', $context->getMethod());
    }
}
