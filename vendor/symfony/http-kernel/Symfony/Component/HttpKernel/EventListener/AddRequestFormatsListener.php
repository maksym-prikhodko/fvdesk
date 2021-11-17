<?php
namespace Symfony\Component\HttpKernel\EventListener;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
class AddRequestFormatsListener implements EventSubscriberInterface
{
    protected $formats;
    public function __construct(array $formats)
    {
        $this->formats = $formats;
    }
    public function onKernelRequest(GetResponseEvent $event)
    {
        foreach ($this->formats as $format => $mimeTypes) {
            $event->getRequest()->setFormat($format, $mimeTypes);
        }
    }
    public static function getSubscribedEvents()
    {
        return array(KernelEvents::REQUEST => 'onKernelRequest');
    }
}
