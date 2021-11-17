<?php
namespace PhpSpec\Listener;
use PhpSpec\Event\SuiteEvent;
use PhpSpec\Process\ReRunner;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
class RerunListener implements EventSubscriberInterface
{
    private $reRunner;
    public function __construct(ReRunner $reRunner)
    {
        $this->reRunner = $reRunner;
    }
    public static function getSubscribedEvents()
    {
        return array('afterSuite' => array('afterSuite', -1000));
    }
    public function afterSuite(SuiteEvent $suiteEvent)
    {
        if ($suiteEvent->isWorthRerunning()) {
            $this->reRunner->reRunSuite();
        }
    }
}
