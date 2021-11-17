<?php
namespace PhpSpec\Listener;
use PhpSpec\CodeGenerator\GeneratorManager;
use PhpSpec\Console\IO;
use PhpSpec\Event\ExampleEvent;
use PhpSpec\Event\SuiteEvent;
use PhpSpec\Exception\Fracture\CollaboratorNotFoundException;
use PhpSpec\Locator\ResourceInterface;
use PhpSpec\Locator\ResourceManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
class CollaboratorNotFoundListener implements EventSubscriberInterface
{
    private $io;
    private $exceptions = array();
    private $resources;
    private $generator;
    public function __construct(IO $io, ResourceManagerInterface $resources, GeneratorManager $generator)
    {
        $this->io = $io;
        $this->resources = $resources;
        $this->generator = $generator;
    }
    public static function getSubscribedEvents()
    {
        return array(
            'afterExample' => array('afterExample', 10),
            'afterSuite'   => array('afterSuite', -10)
        );
    }
    public function afterExample(ExampleEvent $event)
    {
        if (($exception = $event->getException()) &&
            ($exception instanceof CollaboratorNotFoundException)) {
            $this->exceptions[$exception->getCollaboratorName()] = $exception;
        }
    }
    public function afterSuite(SuiteEvent $event)
    {
        if (!$this->io->isCodeGenerationEnabled()) {
            return;
        }
        foreach ($this->exceptions as $exception) {
            $resource = $this->resources->createResource($exception->getCollaboratorName());
            if ($this->resourceIsInSpecNamespace($exception, $resource)) {
                continue;
            }
            if ($this->io->askConfirmation(
                sprintf('Would you like me to generate an interface `%s` for you?', $exception->getCollaboratorName())
            )) {
                $this->generator->generate($resource, 'interface');
                $event->markAsWorthRerunning();
            }
        }
    }
    private function resourceIsInSpecNamespace($exception, $resource)
    {
        return strpos($exception->getCollaboratorName(), $resource->getSpecNamespace()) === 0;
    }
}
