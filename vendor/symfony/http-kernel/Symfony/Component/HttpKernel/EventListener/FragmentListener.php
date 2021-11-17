<?php
namespace Symfony\Component\HttpKernel\EventListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\UriSigner;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
class FragmentListener implements EventSubscriberInterface
{
    private $signer;
    private $fragmentPath;
    public function __construct(UriSigner $signer, $fragmentPath = '/_fragment')
    {
        $this->signer = $signer;
        $this->fragmentPath = $fragmentPath;
    }
    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        if ($this->fragmentPath !== rawurldecode($request->getPathInfo())) {
            return;
        }
        if ($event->isMasterRequest()) {
            $this->validateRequest($request);
        }
        parse_str($request->query->get('_path', ''), $attributes);
        $request->attributes->add($attributes);
        $request->attributes->set('_route_params', array_replace($request->attributes->get('_route_params', array()), $attributes));
        $request->query->remove('_path');
    }
    protected function validateRequest(Request $request)
    {
        if (!$request->isMethodSafe()) {
            throw new AccessDeniedHttpException();
        }
        if ($this->signer->check($request->getSchemeAndHttpHost().$request->getBaseUrl().$request->getPathInfo().(null !== ($qs = $request->server->get('QUERY_STRING')) ? '?'.$qs : ''))) {
            return;
        }
        throw new AccessDeniedHttpException();
    }
    protected function getLocalIpAddresses()
    {
        return array('127.0.0.1', 'fe80::1', '::1');
    }
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => array(array('onKernelRequest', 48)),
        );
    }
}
