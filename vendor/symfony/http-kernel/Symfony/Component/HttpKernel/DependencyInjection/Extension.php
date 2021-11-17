<?php
namespace Symfony\Component\HttpKernel\DependencyInjection;
use Symfony\Component\DependencyInjection\Extension\Extension as BaseExtension;
abstract class Extension extends BaseExtension
{
    private $classes = array();
    public function getClassesToCompile()
    {
        return $this->classes;
    }
    public function addClassesToCompile(array $classes)
    {
        $this->classes = array_merge($this->classes, $classes);
    }
}
