<?php
namespace Symfony\Component\HttpKernel\EventListener;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\HttpCache\SurrogateInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
class SurrogateListener implements EventSubscriberInterface
{
    private $surrogate;
    public function __construct(SurrogateInterface $surrogate = null)
    {
        $this->surrogate = $surrogate;
    }
    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (!$event->isMasterRequest() || null === $this->surrogate) {
            return;
        }
        $this->surrogate->addSurrogateControl($event->getResponse());
    }
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::RESPONSE => 'onKernelResponse',
        );
    }
}
