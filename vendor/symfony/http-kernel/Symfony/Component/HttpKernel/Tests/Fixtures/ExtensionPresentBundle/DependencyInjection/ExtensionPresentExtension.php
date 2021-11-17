<?php
namespace Symfony\Component\HttpKernel\Tests\Fixtures\ExtensionPresentBundle\DependencyInjection;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
class ExtensionPresentExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
    }
}
