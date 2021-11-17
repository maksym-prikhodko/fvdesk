<?php
namespace PhpSpec\Formatter;
use PhpSpec\IO\IOInterface as IO;
use PhpSpec\Formatter\Presenter\PresenterInterface;
use PhpSpec\Listener\StatisticsCollector;
use PhpSpec\Event\ExampleEvent;
use PhpSpec\Event\SuiteEvent;
use PhpSpec\Event\SpecificationEvent;
class JUnitFormatter extends BasicFormatter
{
    protected $testCaseNodes = array();
    protected $testSuiteNodes = array();
    protected $exampleStatusCounts = array();
    protected $jUnitStatuses = array(
        ExampleEvent::PASSED  => 'passed',
        ExampleEvent::PENDING => 'pending',
        ExampleEvent::SKIPPED => 'skipped',
        ExampleEvent::FAILED  => 'failed',
        ExampleEvent::BROKEN  => 'broken',
    );
    protected $resultTags = array(
        ExampleEvent::FAILED  => 'failure',
        ExampleEvent::BROKEN  => 'error',
        ExampleEvent::SKIPPED => 'skipped',
    );
    public function __construct(PresenterInterface $presenter, IO $io, StatisticsCollector $stats)
    {
        parent::__construct($presenter, $io, $stats);
        $this->initTestCaseNodes();
    }
    public function setTestCaseNodes(array $testCaseNodes)
    {
        $this->testCaseNodes = $testCaseNodes;
    }
    public function getTestCaseNodes()
    {
        return $this->testCaseNodes;
    }
    public function setTestSuiteNodes(array $testSuiteNodes)
    {
        $this->testSuiteNodes = $testSuiteNodes;
    }
    public function getTestSuiteNodes()
    {
        return $this->testSuiteNodes;
    }
    public function setExampleStatusCounts(array $exampleStatusCounts)
    {
        $this->exampleStatusCounts = $exampleStatusCounts;
    }
    public function getExampleStatusCounts()
    {
        return $this->exampleStatusCounts;
    }
    public function afterExample(ExampleEvent $event)
    {
        $testCaseNode = sprintf(
            '<testcase name="%s" time="%s" classname="%s" status="%s"',
            $event->getTitle(),
            $event->getTime(),
            $event->getSpecification()->getClassReflection()->getName(),
            $this->jUnitStatuses[$event->getResult()]
        );
        $this->exampleStatusCounts[$event->getResult()]++;
        if (in_array($event->getResult(), array(ExampleEvent::BROKEN, ExampleEvent::FAILED))) {
            $exception = $event->getException();
            $testCaseNode .= sprintf(
                '>'."\n".
                '<%s type="%s" message="%s" />'."\n".
                '<system-err>'."\n".
                '<![CDATA['."\n".
                '%s'."\n".
                ']]>'."\n".
                '</system-err>'."\n".
                '</testcase>',
                $this->resultTags[$event->getResult()],
                get_class($exception),
                htmlspecialchars($exception->getMessage()),
                $exception->getTraceAsString()
            );
        } elseif (ExampleEvent::SKIPPED === $event->getResult()) {
            $testCaseNode .= sprintf(
                '>'."\n".
                '\<skipped><![CDATA[ %s ]]>\</skipped>'."\n".
                '</testcase>',
                htmlspecialchars($event->getException()->getMessage())
            );
        } else {
            $testCaseNode .= ' />';
        }
        $this->testCaseNodes[] = $testCaseNode;
    }
    public function afterSpecification(SpecificationEvent $event)
    {
        $this->testSuiteNodes[] = sprintf(
            '<testsuite name="%s" time="%s" tests="%s" failures="%s" errors="%s" skipped="%s">'."\n".
            '%s'."\n".
            '</testsuite>',
            $event->getTitle(),
            $event->getTime(),
            count($this->testCaseNodes),
            $this->exampleStatusCounts[ExampleEvent::FAILED],
            $this->exampleStatusCounts[ExampleEvent::BROKEN],
            $this->exampleStatusCounts[ExampleEvent::PENDING] + $this->exampleStatusCounts[ExampleEvent::SKIPPED],
            implode("\n", $this->testCaseNodes)
        );
        $this->initTestCaseNodes();
    }
    public function afterSuite(SuiteEvent $event)
    {
        $stats = $this->getStatisticsCollector();
        $this->getIo()->write(sprintf(
            '<?xml version="1.0" encoding="UTF-8" ?>'."\n".
            '<testsuites time="%s" tests="%s" failures="%s" errors="%s">'."\n".
            '%s'."\n".
            '</testsuites>',
            $event->getTime(),
            $stats->getEventsCount(),
            count($stats->getFailedEvents()),
            count($stats->getBrokenEvents()),
            implode("\n", $this->testSuiteNodes)
        ));
    }
    protected function initTestCaseNodes()
    {
        $this->testCaseNodes       = array();
        $this->exampleStatusCounts = array(
            ExampleEvent::PASSED  => 0,
            ExampleEvent::PENDING => 0,
            ExampleEvent::SKIPPED => 0,
            ExampleEvent::FAILED  => 0,
            ExampleEvent::BROKEN  => 0,
        );
    }
}
