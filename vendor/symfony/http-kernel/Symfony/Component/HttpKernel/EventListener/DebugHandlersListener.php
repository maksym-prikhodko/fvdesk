<?php
namespace Symfony\Component\HttpKernel\EventListener;
use Psr\Log\LoggerInterface;
use Symfony\Component\Debug\ErrorHandler;
use Symfony\Component\Debug\ExceptionHandler;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleEvent;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
class DebugHandlersListener implements EventSubscriberInterface
{
    private $exceptionHandler;
    private $logger;
    private $levels;
    private $throwAt;
    private $scream;
    private $fileLinkFormat;
    private $firstCall = true;
    public function __construct($exceptionHandler, LoggerInterface $logger = null, $levels = null, $throwAt = -1, $scream = true, $fileLinkFormat = null)
    {
        $this->exceptionHandler = $exceptionHandler;
        $this->logger = $logger;
        $this->levels = $levels;
        $this->throwAt = is_numeric($throwAt) ? (int) $throwAt : (null === $throwAt ? null : ($throwAt ? -1 : null));
        $this->scream = (bool) $scream;
        $this->fileLinkFormat = $fileLinkFormat ?: ini_get('xdebug.file_link_format') ?: get_cfg_var('xdebug.file_link_format');
    }
    public function configure(Event $event = null)
    {
        if (!$this->firstCall) {
            return;
        }
        $this->firstCall = false;
        if ($this->logger || null !== $this->throwAt) {
            $handler = set_error_handler('var_dump', 0);
            $handler = is_array($handler) ? $handler[0] : null;
            restore_error_handler();
            if ($handler instanceof ErrorHandler) {
                if ($this->logger) {
                    $handler->setDefaultLogger($this->logger, $this->levels);
                    if (is_array($this->levels)) {
                        $scream = 0;
                        foreach ($this->levels as $type => $log) {
                            $scream |= $type;
                        }
                    } else {
                        $scream = null === $this->levels ? E_ALL | E_STRICT : $this->levels;
                    }
                    if ($this->scream) {
                        $handler->screamAt($scream);
                    }
                    $this->logger = $this->levels = null;
                }
                if (null !== $this->throwAt) {
                    $handler->throwAt($this->throwAt, true);
                }
            }
        }
        if (!$this->exceptionHandler) {
            if ($event instanceof KernelEvent) {
                $this->exceptionHandler = array($event->getKernel(), 'terminateWithException');
            } elseif ($event instanceof ConsoleEvent && $app = $event->getCommand()->getApplication()) {
                $output = $event->getOutput();
                if ($output instanceof ConsoleOutputInterface) {
                    $output = $output->getErrorOutput();
                }
                $this->exceptionHandler = function ($e) use ($app, $output) {
                    $app->renderException($e, $output);
                };
            }
        }
        if ($this->exceptionHandler) {
            $handler = set_exception_handler('var_dump');
            $handler = is_array($handler) ? $handler[0] : null;
            restore_exception_handler();
            if ($handler instanceof ErrorHandler) {
                $h = $handler->setExceptionHandler('var_dump') ?: $this->exceptionHandler;
                $handler->setExceptionHandler($h);
                $handler = is_array($h) ? $h[0] : null;
            }
            if ($handler instanceof ExceptionHandler) {
                $handler->setHandler($this->exceptionHandler);
                if (null !== $this->fileLinkFormat) {
                    $handler->setFileLinkFormat($this->fileLinkFormat);
                }
            }
            $this->exceptionHandler = null;
        }
    }
    public static function getSubscribedEvents()
    {
        $events = array(KernelEvents::REQUEST => array('configure', 2048));
        if (defined('Symfony\Component\Console\ConsoleEvents::COMMAND')) {
            $events[ConsoleEvents::COMMAND] = array('configure', 2048);
        }
        return $events;
    }
}
