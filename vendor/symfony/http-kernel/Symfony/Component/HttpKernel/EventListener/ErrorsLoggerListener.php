<?php
namespace Symfony\Component\HttpKernel\EventListener;
use Psr\Log\LoggerInterface;
use Symfony\Component\Debug\ErrorHandler;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
class ErrorsLoggerListener implements EventSubscriberInterface
{
    private $channel;
    private $logger;
    public function __construct($channel, LoggerInterface $logger = null)
    {
        $this->channel = $channel;
        $this->logger = $logger;
    }
    public function injectLogger()
    {
        if (null !== $this->logger) {
            ErrorHandler::setLogger($this->logger, $this->channel);
            $this->logger = null;
        }
    }
    public static function getSubscribedEvents()
    {
        return array(KernelEvents::REQUEST => array('injectLogger', 2048));
    }
}
