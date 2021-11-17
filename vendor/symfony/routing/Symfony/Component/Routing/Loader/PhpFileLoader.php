<?php
namespace Symfony\Component\Routing\Loader;
use Symfony\Component\Config\Loader\FileLoader;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Routing\RouteCollection;
class PhpFileLoader extends FileLoader
{
    public function load($file, $type = null)
    {
        $path = $this->locator->locate($file);
        $this->setCurrentDir(dirname($path));
        $collection = self::includeFile($path, $this);
        $collection->addResource(new FileResource($path));
        return $collection;
    }
    public function supports($resource, $type = null)
    {
        return is_string($resource) && 'php' === pathinfo($resource, PATHINFO_EXTENSION) && (!$type || 'php' === $type);
    }
    private static function includeFile($file, PhpFileLoader $loader)
    {
        return include $file;
    }
}
