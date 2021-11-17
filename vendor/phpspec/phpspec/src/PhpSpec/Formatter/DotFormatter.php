<?php
namespace PhpSpec\Formatter;
use PhpSpec\Event\SuiteEvent;
use PhpSpec\Event\ExampleEvent;
class DotFormatter extends ConsoleFormatter
{
    private $examplesCount = 0;
    public function beforeSuite(SuiteEvent $event)
    {
        $this->examplesCount = count($event->getSuite());
    }
    public function afterExample(ExampleEvent $event)
    {
        $io = $this->getIO();
        $eventsCount = $this->getStatisticsCollector()->getEventsCount();
        if ($eventsCount === 1) {
            $io->writeln();
        }
        switch ($event->getResult()) {
            case ExampleEvent::PASSED:
                $io->write('<passed>.</passed>');
                break;
            case ExampleEvent::PENDING:
                $io->write('<pending>P</pending>');
                break;
            case ExampleEvent::SKIPPED:
                $io->write('<skipped>S</skipped>');
                break;
            case ExampleEvent::FAILED:
                $io->write('<failed>F</failed>');
                break;
            case ExampleEvent::BROKEN:
                $io->write('<broken>B</broken>');
                break;
        }
        if ($eventsCount % 50 === 0) {
            $length = strlen((string) $this->examplesCount);
            $format = sprintf(' %%%dd / %%%dd', $length, $length);
            $io->write(sprintf($format, $eventsCount, $this->examplesCount));
            if ($eventsCount !== $this->examplesCount) {
                $io->writeLn();
            }
        }
    }
    public function afterSuite(SuiteEvent $event)
    {
        $this->getIO()->writeln("\n");
        $this->outputExceptions();
        $this->outputSuiteSummary($event);
    }
    private function outputExceptions()
    {
        $stats = $this->getStatisticsCollector();
        $notPassed = array_filter(array(
            'failed' => $stats->getFailedEvents(),
            'broken' => $stats->getBrokenEvents(),
            'pending' => $stats->getPendingEvents(),
            'skipped' => $stats->getSkippedEvents(),
        ));
        foreach ($notPassed as $events) {
            array_map(array($this, 'printException'), $events);
        }
    }
    private function outputSuiteSummary(SuiteEvent $event)
    {
        $this->outputTotalSpecCount();
        $this->outputTotalExamplesCount();
        $this->outputSpecificExamplesCount();
        $this->getIO()->writeln(sprintf("\n%sms", round($event->getTime() * 1000)));
    }
    private function plural($count)
    {
        return $count !== 1 ? 's' : '';
    }
    private function outputTotalSpecCount()
    {
        $count = $this->getStatisticsCollector()->getTotalSpecs();
        $this->getIO()->writeln(sprintf("%d spec%s", $count, $this->plural($count)));
    }
    private function outputTotalExamplesCount()
    {
        $count = $this->getStatisticsCollector()->getEventsCount();
        $this->getIO()->write(sprintf("%d example%s ", $count, $this->plural($count)));
    }
    private function outputSpecificExamplesCount()
    {
        $typesWithEvents = array_filter($this->getStatisticsCollector()->getCountsHash());
        $counts = array();
        foreach ($typesWithEvents as $type => $count) {
            $counts[] = sprintf('<%s>%d %s</%s>', $type, $count, $type, $type);
        }
        if (count($counts)) {
            $this->getIO()->write(sprintf("(%s)", implode(', ', $counts)));
        }
    }
}
