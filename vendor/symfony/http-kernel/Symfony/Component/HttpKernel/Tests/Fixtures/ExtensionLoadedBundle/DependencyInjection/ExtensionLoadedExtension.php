<?php
namespace Symfony\Component\HttpKernel\Tests\Fixtures\ExtensionLoadedBundle\DependencyInjection;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
class ExtensionLoadedExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
    }
}
