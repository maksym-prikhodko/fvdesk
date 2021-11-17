<?php
namespace League\Flysystem\Adapter;
use League\Flysystem\AdapterInterface;
abstract class AbstractAdapter implements AdapterInterface
{
    protected $pathPrefix;
    protected $pathSeparator = '/';
    public function setPathPrefix($prefix)
    {
        $is_empty = empty($prefix);
        if (! $is_empty) {
            $prefix = rtrim($prefix, $this->pathSeparator).$this->pathSeparator;
        }
        $this->pathPrefix = $is_empty ? null : $prefix;
    }
    public function getPathPrefix()
    {
        return $this->pathPrefix;
    }
    public function applyPathPrefix($path)
    {
        $path = ltrim($path, '\\/');
        if (strlen($path) === 0) {
            return $this->getPathPrefix() ?: '';
        }
        if ($prefix = $this->getPathPrefix()) {
            $path = $prefix.$path;
        }
        return $path;
    }
    public function removePathPrefix($path)
    {
        $pathPrefix = $this->getPathPrefix();
        if ($pathPrefix === null) {
            return $path;
        }
        return substr($path, strlen($pathPrefix));
    }
}
