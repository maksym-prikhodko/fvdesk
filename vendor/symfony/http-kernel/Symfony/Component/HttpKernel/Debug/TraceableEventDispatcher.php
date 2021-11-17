<?php
namespace Symfony\Component\HttpKernel\Debug;
use Symfony\Component\EventDispatcher\Debug\TraceableEventDispatcher as BaseTraceableEventDispatcher;
use Symfony\Component\HttpKernel\Profiler\Profiler;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\Event;
class TraceableEventDispatcher extends BaseTraceableEventDispatcher
{
    public function setProfiler(Profiler $profiler = null)
    {
    }
    protected function preDispatch($eventName, Event $event)
    {
        switch ($eventName) {
            case KernelEvents::REQUEST:
                $this->stopwatch->openSection();
                break;
            case KernelEvents::VIEW:
            case KernelEvents::RESPONSE:
                if ($this->stopwatch->isStarted('controller')) {
                    $this->stopwatch->stop('controller');
                }
                break;
            case KernelEvents::TERMINATE:
                $token = $event->getResponse()->headers->get('X-Debug-Token');
                try {
                    $this->stopwatch->openSection($token);
                } catch (\LogicException $e) {
                }
                break;
        }
    }
    protected function postDispatch($eventName, Event $event)
    {
        switch ($eventName) {
            case KernelEvents::CONTROLLER:
                $this->stopwatch->start('controller', 'section');
                break;
            case KernelEvents::RESPONSE:
                $token = $event->getResponse()->headers->get('X-Debug-Token');
                $this->stopwatch->stopSection($token);
                break;
            case KernelEvents::TERMINATE:
                $token = $event->getResponse()->headers->get('X-Debug-Token');
                try {
                    $this->stopwatch->stopSection($token);
                } catch (\LogicException $e) {
                }
                break;
        }
    }
}
