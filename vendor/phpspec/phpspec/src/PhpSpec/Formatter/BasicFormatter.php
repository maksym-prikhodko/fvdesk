<?php
namespace PhpSpec\Formatter;
use PhpSpec\IO\IOInterface as IO;
use PhpSpec\Formatter\Presenter\PresenterInterface;
use PhpSpec\Listener\StatisticsCollector;
use PhpSpec\Event\SuiteEvent;
use PhpSpec\Event\SpecificationEvent;
use PhpSpec\Event\ExampleEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
abstract class BasicFormatter implements EventSubscriberInterface
{
    private $io;
    private $presenter;
    private $stats;
    public function __construct(PresenterInterface $presenter, IO $io, StatisticsCollector $stats)
    {
        $this->presenter = $presenter;
        $this->io = $io;
        $this->stats = $stats;
    }
    public static function getSubscribedEvents()
    {
        $events = array(
            'beforeSuite', 'afterSuite',
            'beforeExample', 'afterExample',
            'beforeSpecification', 'afterSpecification'
        );
        return array_combine($events, $events);
    }
    protected function getIO()
    {
        return $this->io;
    }
    protected function getPresenter()
    {
        return $this->presenter;
    }
    protected function getStatisticsCollector()
    {
        return $this->stats;
    }
    public function beforeSuite(SuiteEvent $event)
    {
    }
    public function afterSuite(SuiteEvent $event)
    {
    }
    public function beforeExample(ExampleEvent $event)
    {
    }
    public function afterExample(ExampleEvent $event)
    {
    }
    public function beforeSpecification(SpecificationEvent $event)
    {
    }
    public function afterSpecification(SpecificationEvent $event)
    {
    }
}
