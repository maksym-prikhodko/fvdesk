<?php
namespace Symfony\Component\EventDispatcher\Debug;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Stopwatch\Stopwatch;
use Psr\Log\LoggerInterface;
class TraceableEventDispatcher implements TraceableEventDispatcherInterface
{
    protected $logger;
    protected $stopwatch;
    private $called;
    private $dispatcher;
    public function __construct(EventDispatcherInterface $dispatcher, Stopwatch $stopwatch, LoggerInterface $logger = null)
    {
        $this->dispatcher = $dispatcher;
        $this->stopwatch = $stopwatch;
        $this->logger = $logger;
        $this->called = array();
    }
    public function addListener($eventName, $listener, $priority = 0)
    {
        $this->dispatcher->addListener($eventName, $listener, $priority);
    }
    public function addSubscriber(EventSubscriberInterface $subscriber)
    {
        $this->dispatcher->addSubscriber($subscriber);
    }
    public function removeListener($eventName, $listener)
    {
        return $this->dispatcher->removeListener($eventName, $listener);
    }
    public function removeSubscriber(EventSubscriberInterface $subscriber)
    {
        return $this->dispatcher->removeSubscriber($subscriber);
    }
    public function getListeners($eventName = null)
    {
        return $this->dispatcher->getListeners($eventName);
    }
    public function hasListeners($eventName = null)
    {
        return $this->dispatcher->hasListeners($eventName);
    }
    public function dispatch($eventName, Event $event = null)
    {
        if (null === $event) {
            $event = new Event();
        }
        $this->preProcess($eventName);
        $this->preDispatch($eventName, $event);
        $e = $this->stopwatch->start($eventName, 'section');
        $this->dispatcher->dispatch($eventName, $event);
        if ($e->isStarted()) {
            $e->stop();
        }
        $this->postDispatch($eventName, $event);
        $this->postProcess($eventName);
        return $event;
    }
    public function getCalledListeners()
    {
        $called = array();
        foreach ($this->called as $eventName => $listeners) {
            foreach ($listeners as $listener) {
                $info = $this->getListenerInfo($listener->getWrappedListener(), $eventName);
                $called[$eventName.'.'.$info['pretty']] = $info;
            }
        }
        return $called;
    }
    public function getNotCalledListeners()
    {
        try {
            $allListeners = $this->getListeners();
        } catch (\Exception $e) {
            if (null !== $this->logger) {
                $this->logger->info(sprintf('An exception was thrown while getting the uncalled listeners (%s)', $e->getMessage()), array('exception' => $e));
            }
            return array();
        }
        $notCalled = array();
        foreach ($allListeners as $eventName => $listeners) {
            foreach ($listeners as $listener) {
                $called = false;
                if (isset($this->called[$eventName])) {
                    foreach ($this->called[$eventName] as $l) {
                        if ($l->getWrappedListener() === $listener) {
                            $called = true;
                            break;
                        }
                    }
                }
                if (!$called) {
                    $info = $this->getListenerInfo($listener, $eventName);
                    $notCalled[$eventName.'.'.$info['pretty']] = $info;
                }
            }
        }
        return $notCalled;
    }
    public function __call($method, $arguments)
    {
        return call_user_func_array(array($this->dispatcher, $method), $arguments);
    }
    protected function preDispatch($eventName, Event $event)
    {
    }
    protected function postDispatch($eventName, Event $event)
    {
    }
    private function preProcess($eventName)
    {
        foreach ($this->dispatcher->getListeners($eventName) as $listener) {
            $this->dispatcher->removeListener($eventName, $listener);
            $info = $this->getListenerInfo($listener, $eventName);
            $name = isset($info['class']) ? $info['class'] : $info['type'];
            $this->dispatcher->addListener($eventName, new WrappedListener($listener, $name, $this->stopwatch, $this));
        }
    }
    private function postProcess($eventName)
    {
        $skipped = false;
        foreach ($this->dispatcher->getListeners($eventName) as $listener) {
            if (!$listener instanceof WrappedListener) { 
                continue;
            }
            $this->dispatcher->removeListener($eventName, $listener);
            $this->dispatcher->addListener($eventName, $listener->getWrappedListener());
            $info = $this->getListenerInfo($listener->getWrappedListener(), $eventName);
            if ($listener->wasCalled()) {
                if (null !== $this->logger) {
                    $this->logger->debug(sprintf('Notified event "%s" to listener "%s".', $eventName, $info['pretty']));
                }
                if (!isset($this->called[$eventName])) {
                    $this->called[$eventName] = new \SplObjectStorage();
                }
                $this->called[$eventName]->attach($listener);
            }
            if (null !== $this->logger && $skipped) {
                $this->logger->debug(sprintf('Listener "%s" was not called for event "%s".', $info['pretty'], $eventName));
            }
            if ($listener->stoppedPropagation()) {
                if (null !== $this->logger) {
                    $this->logger->debug(sprintf('Listener "%s" stopped propagation of the event "%s".', $info['pretty'], $eventName));
                }
                $skipped = true;
            }
        }
    }
    private function getListenerInfo($listener, $eventName)
    {
        $info = array(
            'event' => $eventName,
        );
        if ($listener instanceof \Closure) {
            $info += array(
                'type' => 'Closure',
                'pretty' => 'closure',
            );
        } elseif (is_string($listener)) {
            try {
                $r = new \ReflectionFunction($listener);
                $file = $r->getFileName();
                $line = $r->getStartLine();
            } catch (\ReflectionException $e) {
                $file = null;
                $line = null;
            }
            $info += array(
                'type' => 'Function',
                'function' => $listener,
                'file' => $file,
                'line' => $line,
                'pretty' => $listener,
            );
        } elseif (is_array($listener) || (is_object($listener) && is_callable($listener))) {
            if (!is_array($listener)) {
                $listener = array($listener, '__invoke');
            }
            $class = is_object($listener[0]) ? get_class($listener[0]) : $listener[0];
            try {
                $r = new \ReflectionMethod($class, $listener[1]);
                $file = $r->getFileName();
                $line = $r->getStartLine();
            } catch (\ReflectionException $e) {
                $file = null;
                $line = null;
            }
            $info += array(
                'type' => 'Method',
                'class' => $class,
                'method' => $listener[1],
                'file' => $file,
                'line' => $line,
                'pretty' => $class.'::'.$listener[1],
            );
        }
        return $info;
    }
}
