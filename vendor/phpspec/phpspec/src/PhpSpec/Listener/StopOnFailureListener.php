<?php
namespace PhpSpec\Listener;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use PhpSpec\Event\ExampleEvent;
use PhpSpec\Exception\Example\StopOnFailureException;
use PhpSpec\Console\IO;
class StopOnFailureListener implements EventSubscriberInterface
{
    private $io;
    public function __construct(IO $io)
    {
        $this->io = $io;
    }
    public static function getSubscribedEvents()
    {
        return array(
            'afterExample' => array('afterExample', -100),
        );
    }
    public function afterExample(ExampleEvent $event)
    {
        if (!$this->io->isStopOnFailureEnabled()) {
            return;
        }
        if ($event->getResult() === ExampleEvent::FAILED
         || $event->getResult() === ExampleEvent::BROKEN) {
            throw new StopOnFailureException('Example failed', 0, null, $event->getResult());
        }
    }
}
