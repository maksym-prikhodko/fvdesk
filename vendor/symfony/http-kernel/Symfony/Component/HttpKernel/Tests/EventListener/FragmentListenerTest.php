<?php
namespace Symfony\Component\HttpKernel\Tests\EventListener;
use Symfony\Component\HttpKernel\EventListener\FragmentListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\UriSigner;
class FragmentListenerTest extends \PHPUnit_Framework_TestCase
{
    public function testOnlyTriggeredOnFragmentRoute()
    {
        $request = Request::create('http:
        $listener = new FragmentListener(new UriSigner('foo'));
        $event = $this->createGetResponseEvent($request);
        $expected = $request->attributes->all();
        $listener->onKernelRequest($event);
        $this->assertEquals($expected, $request->attributes->all());
        $this->assertTrue($request->query->has('_path'));
    }
    public function testAccessDeniedWithNonSafeMethods()
    {
        $request = Request::create('http:
        $listener = new FragmentListener(new UriSigner('foo'));
        $event = $this->createGetResponseEvent($request);
        $listener->onKernelRequest($event);
    }
    public function testAccessDeniedWithWrongSignature()
    {
        $request = Request::create('http:
        $listener = new FragmentListener(new UriSigner('foo'));
        $event = $this->createGetResponseEvent($request);
        $listener->onKernelRequest($event);
    }
    public function testWithSignature()
    {
        $signer = new UriSigner('foo');
        $request = Request::create($signer->sign('http:
        $listener = new FragmentListener($signer);
        $event = $this->createGetResponseEvent($request);
        $listener->onKernelRequest($event);
        $this->assertEquals(array('foo' => 'bar', '_controller' => 'foo'), $request->attributes->get('_route_params'));
        $this->assertFalse($request->query->has('_path'));
    }
    private function createGetResponseEvent(Request $request)
    {
        return new GetResponseEvent($this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface'), $request, HttpKernelInterface::MASTER_REQUEST);
    }
}
