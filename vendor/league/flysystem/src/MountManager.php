<?php
namespace League\Flysystem;
use InvalidArgumentException;
use League\Flysystem\Plugin\PluggableTrait;
use League\Flysystem\Plugin\PluginNotFoundException;
use LogicException;
class MountManager
{
    use PluggableTrait;
    protected $filesystems = [];
    public function __construct(array $filesystems = [])
    {
        $this->mountFilesystems($filesystems);
    }
    public function mountFilesystems(array $filesystems)
    {
        foreach ($filesystems as $prefix => $filesystem) {
            $this->mountFilesystem($prefix, $filesystem);
        }
        return $this;
    }
    public function mountFilesystem($prefix, FilesystemInterface $filesystem)
    {
        if (! is_string($prefix)) {
            throw new InvalidArgumentException(__METHOD__.' expects argument #1 to be a string.');
        }
        $this->filesystems[$prefix] = $filesystem;
        return $this;
    }
    public function getFilesystem($prefix)
    {
        if (! isset($this->filesystems[$prefix])) {
            throw new LogicException('No filesystem mounted with prefix '.$prefix);
        }
        return $this->filesystems[$prefix];
    }
    public function filterPrefix(array $arguments)
    {
        if (empty($arguments)) {
            throw new LogicException('At least one argument needed');
        }
        $path = array_shift($arguments);
        if (! is_string($path)) {
            throw new InvalidArgumentException('First argument should be a string');
        }
        if (! preg_match('#^[a-zA-Z0-9]+\:\/\/.*#', $path)) {
            throw new InvalidArgumentException('No prefix detected in for path: '.$path);
        }
        list($prefix, $path) = explode(':
        array_unshift($arguments, $path);
        return [$prefix, $arguments];
    }
    public function listContents($directory = '', $recursive = false)
    {
        list($prefix, $arguments) = $this->filterPrefix([$directory]);
        $filesystem = $this->getFilesystem($prefix);
        $directory = array_shift($arguments);
        $result = $filesystem->listContents($directory, $recursive);
        foreach ($result as &$file) {
            $file['filesystem'] = $prefix;
        }
        return $result;
    }
    public function __call($method, $arguments)
    {
        list($prefix, $arguments) = $this->filterPrefix($arguments);
        return $this->invokePluginOnFilesystem($method, $arguments, $prefix);
    }
    public function copy($from, $to)
    {
        list($prefixFrom, $arguments) = $this->filterPrefix([$from]);
        $fsFrom = $this->getFilesystem($prefixFrom);
        $buffer = call_user_func_array([$fsFrom, 'readStream'], $arguments);
        if ($buffer === false) {
            return false;
        }
        list($prefixTo, $arguments) = $this->filterPrefix([$to]);
        $fsTo = $this->getFilesystem($prefixTo);
        $result =  call_user_func_array([$fsTo, 'writeStream'], array_merge($arguments, [$buffer]));
        if (is_resource($buffer)) {
            fclose($buffer);
        }
        return $result;
    }
    public function listWith(array $keys = [], $directory = '', $recursive = false)
    {
        list($prefix, $arguments) = $this->filterPrefix([$directory]);
        $directory = $arguments[0];
        $arguments = [$keys, $directory, $recursive];
        return $this->invokePluginOnFilesystem('listWith', $arguments, $prefix);
    }
    public function move($from, $to)
    {
        $copied = $this->copy($from, $to);
        if ($copied) {
            return $this->delete($from);
        }
        return false;
    }
    public function invokePluginOnFilesystem($method, $arguments, $prefix)
    {
        $filesystem = $this->getFilesystem($prefix);
        try {
            return $this->invokePlugin($method, $arguments, $filesystem);
        } catch (PluginNotFoundException $e) {
        }
        $callback = [$filesystem, $method];
        return call_user_func_array($callback, $arguments);
    }
}
