<?php
namespace PhpSpec\Formatter;
use PhpSpec\Console\IO;
use PhpSpec\Event\ExampleEvent;
use PhpSpec\Exception\Example\PendingException;
use PhpSpec\Exception\Example\SkippingException;
use PhpSpec\Formatter\Presenter\PresenterInterface;
use PhpSpec\Listener\StatisticsCollector;
class ConsoleFormatter extends BasicFormatter
{
    private $io;
    public function __construct(PresenterInterface $presenter, IO $io, StatisticsCollector $stats)
    {
        parent::__construct($presenter, $io, $stats);
        $this->io = $io;
    }
    protected function getIO()
    {
        return $this->io;
    }
    protected function printException(ExampleEvent $event)
    {
        if (null === $exception = $event->getException()) {
            return;
        }
        if ($exception instanceof PendingException) {
            $this->printSpecificException($event, 'pending');
        } elseif ($exception instanceof SkippingException) {
            if ($this->io->isVerbose()) {
                $this->printSpecificException($event, 'skipped ');
            }
        } elseif (ExampleEvent::FAILED === $event->getResult()) {
            $this->printSpecificException($event, 'failed');
        } else {
            $this->printSpecificException($event, 'broken');
        }
    }
    protected function printSpecificException(ExampleEvent $event, $type)
    {
        $title = str_replace('\\', DIRECTORY_SEPARATOR, $event->getSpecification()->getTitle());
        $message = $this->getPresenter()->presentException($event->getException(), $this->io->isVerbose());
        foreach (explode("\n", wordwrap($title, $this->io->getBlockWidth(), "\n", true)) as $line) {
            $this->io->writeln(sprintf('<%s-bg>%s</%s-bg>', $type, str_pad($line, $this->io->getBlockWidth()), $type));
        }
        $this->io->writeln(sprintf(
            '<lineno>%4d</lineno>  <%s>- %s</%s>',
            $event->getExample()->getFunctionReflection()->getStartLine(),
            $type,
            $event->getExample()->getTitle(),
            $type
        ));
        $this->io->writeln(sprintf('<%s>%s</%s>', $type, lcfirst($message), $type), 6);
        $this->io->writeln();
    }
}
