<?php
namespace PhpSpec\Loader;
use PhpSpec\Util\MethodAnalyser;
use PhpSpec\Locator\ResourceManagerInterface;
use ReflectionClass;
use ReflectionMethod;
class ResourceLoader
{
    private $manager;
    private $methodAnalyser;
    public function __construct(ResourceManagerInterface $manager, MethodAnalyser $methodAnalyser = null)
    {
        $this->manager = $manager;
        $this->methodAnalyser = $methodAnalyser ?: new MethodAnalyser();
    }
    public function load($locator, $line = null)
    {
        $suite = new Suite();
        foreach ($this->manager->locateResources($locator) as $resource) {
            if (!class_exists($resource->getSpecClassname()) && is_file($resource->getSpecFilename())) {
                require_once $resource->getSpecFilename();
            }
            if (!class_exists($resource->getSpecClassname())) {
                continue;
            }
            $reflection = new ReflectionClass($resource->getSpecClassname());
            if ($reflection->isAbstract()) {
                continue;
            }
            if (!$reflection->implementsInterface('PhpSpec\SpecificationInterface')) {
                continue;
            }
            $spec = new Node\SpecificationNode($resource->getSrcClassname(), $reflection, $resource);
            foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                if (!preg_match('/^(it|its)[^a-zA-Z]/', $method->getName())) {
                    continue;
                }
                if (null !== $line && !$this->lineIsInsideMethod($line, $method)) {
                    continue;
                }
                $example = new Node\ExampleNode(str_replace('_', ' ', $method->getName()), $method);
                if ($this->methodAnalyser->reflectionMethodIsEmpty($method)) {
                    $example->markAsPending();
                }
                $spec->addExample($example);
            }
            $suite->addSpecification($spec);
        }
        return $suite;
    }
    private function lineIsInsideMethod($line, ReflectionMethod $method)
    {
        $line = intval($line);
        return $line >= $method->getStartLine() && $line <= $method->getEndLine();
    }
}
