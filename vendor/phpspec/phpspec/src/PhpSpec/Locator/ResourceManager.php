<?php
namespace PhpSpec\Locator;
use PhpSpec\Exception\Locator\ResourceCreationException;
class ResourceManager implements ResourceManagerInterface
{
    private $locators = array();
    public function registerLocator(ResourceLocatorInterface $locator)
    {
        $this->locators[] = $locator;
        @usort($this->locators, function ($locator1, $locator2) {
            return $locator2->getPriority() - $locator1->getPriority();
        });
    }
    public function locateResources($query)
    {
        $resources = array();
        foreach ($this->locators as $locator) {
            if (empty($query)) {
                $resources = array_merge($resources, $locator->getAllResources());
                continue;
            }
            if (!$locator->supportsQuery($query)) {
                continue;
            }
            $resources = array_merge($resources, $locator->findResources($query));
        }
        return $this->removeDuplicateResources($resources);
    }
    public function createResource($classname)
    {
        foreach ($this->locators as $locator) {
            if ($locator->supportsClass($classname)) {
                return $locator->createResource($classname);
            }
        }
        throw new ResourceCreationException(
            sprintf(
                'Can not find appropriate suite scope for class `%s`.',
                $classname
            )
        );
    }
    private function removeDuplicateResources(array $resources)
    {
        $filteredResources = array();
        foreach ($resources as $resource) {
            if (!array_key_exists($resource->getSpecClassname(), $filteredResources)) {
                $filteredResources[$resource->getSpecClassname()] = $resource;
            }
        }
        return array_values($filteredResources);
    }
}
