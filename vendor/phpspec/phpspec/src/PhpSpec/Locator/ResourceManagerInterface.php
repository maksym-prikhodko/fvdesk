<?php
namespace PhpSpec\Locator;
interface ResourceManagerInterface
{
    public function locateResources($query);
    public function createResource($classname);
}
