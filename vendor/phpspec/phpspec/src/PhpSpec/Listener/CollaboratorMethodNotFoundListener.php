<?php
namespace PhpSpec\Listener;
use PhpSpec\CodeGenerator\GeneratorManager;
use PhpSpec\Console\IO;
use PhpSpec\Event\ExampleEvent;
use PhpSpec\Event\SuiteEvent;
use PhpSpec\Exception\Locator\ResourceCreationException;
use PhpSpec\Locator\ResourceManagerInterface;
use Prophecy\Argument\ArgumentsWildcard;
use Prophecy\Exception\Doubler\MethodNotFoundException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
class CollaboratorMethodNotFoundListener implements EventSubscriberInterface
{
    const PROMPT = 'Would you like me to generate a method signature `%s::%s()` for you?';
    private $io;
    private $interfaces = array();
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
            'afterSuite' => array('afterSuite', -10)
        );
    }
    public function afterExample(ExampleEvent $event)
    {
        if (!$exception = $this->getMethodNotFoundException($event)) {
            return;
        }
        if (!$interface = $this->getDoubledInterface($exception->getClassName())) {
            return;
        }
        if (!array_key_exists($interface, $this->interfaces)) {
            $this->interfaces[$interface] = array();
        }
        $this->interfaces[$interface][$exception->getMethodName()] = $exception->getArguments();
    }
    private function getDoubledInterface($classname)
    {
        if (class_parents($classname) !== array('stdClass'=>'stdClass')) {
            return;
        }
        $interfaces = array_filter(class_implements($classname),
            function ($interface) {
                return !preg_match('/^Prophecy/', $interface);
            }
        );
        if (count($interfaces) !== 1) {
            return;
        }
        return current($interfaces);
    }
    public function afterSuite(SuiteEvent $event)
    {
        foreach ($this->interfaces as $interface => $methods) {
            try {
                $resource = $this->resources->createResource($interface);
            } catch (ResourceCreationException $e) {
                continue;
            }
            foreach ($methods as $method => $arguments) {
                if ($this->io->askConfirmation(sprintf(self::PROMPT, $interface, $method))) {
                    $this->generator->generate(
                        $resource,
                        'method-signature',
                        array(
                            'name' => $method,
                            'arguments' => $this->getRealArguments($arguments)
                        )
                    );
                    $event->markAsWorthRerunning();
                }
            }
        }
    }
    private function getRealArguments($prophecyArguments)
    {
        if ($prophecyArguments instanceof ArgumentsWildcard) {
            return $prophecyArguments->getTokens();
        }
        return array();
    }
    private function getMethodNotFoundException(ExampleEvent $event)
    {
        if ($this->io->isCodeGenerationEnabled()
            && ($exception = $event->getException())
            && $exception instanceof MethodNotFoundException) {
            return $exception;
        }
    }
}
