<?php
namespace League\Flysystem;
use BadMethodCallException;
use InvalidArgumentException;
use League\Flysystem\Plugin\PluggableTrait;
use League\Flysystem\Plugin\PluginNotFoundException;
class Filesystem implements FilesystemInterface
{
    use PluggableTrait;
    protected $adapter;
    protected $config;
    public function __construct(AdapterInterface $adapter, $config = null)
    {
        $this->adapter = $adapter;
        $this->config = Util::ensureConfig($config);
    }
    public function getAdapter()
    {
        return $this->adapter;
    }
    public function getConfig()
    {
        return $this->config;
    }
    public function has($path)
    {
        $path = Util::normalizePath($path);
        return (bool) $this->adapter->has($path);
    }
    public function write($path, $contents, array $config = [])
    {
        $path = Util::normalizePath($path);
        $this->assertAbsent($path);
        $config = $this->prepareConfig($config);
        return (bool) $this->adapter->write($path, $contents, $config);
    }
    public function writeStream($path, $resource, array $config = [])
    {
        if (! is_resource($resource)) {
            throw new InvalidArgumentException(__METHOD__.' expects argument #2 to be a valid resource.');
        }
        $path = Util::normalizePath($path);
        $this->assertAbsent($path);
        $config = $this->prepareConfig($config);
        Util::rewindStream($resource);
        return (bool) $this->adapter->writeStream($path, $resource, $config);
    }
    public function put($path, $contents, array $config = [])
    {
        $path = Util::normalizePath($path);
        if ($this->has($path)) {
            return $this->update($path, $contents, $config);
        }
        return $this->write($path, $contents, $config);
    }
    public function putStream($path, $resource, array $config = [])
    {
        $path = Util::normalizePath($path);
        if ($this->has($path)) {
            return $this->updateStream($path, $resource, $config);
        }
        return $this->writeStream($path, $resource, $config);
    }
    public function readAndDelete($path)
    {
        $path = Util::normalizePath($path);
        $this->assertPresent($path);
        $contents = $this->read($path);
        if ($contents === false) {
            return false;
        }
        $this->delete($path);
        return $contents;
    }
    public function update($path, $contents, array $config = [])
    {
        $path = Util::normalizePath($path);
        $config = $this->prepareConfig($config);
        $this->assertPresent($path);
        return (bool) $this->adapter->update($path, $contents, $config);
    }
    public function updateStream($path, $resource, array $config = [])
    {
        if (! is_resource($resource)) {
            throw new InvalidArgumentException(__METHOD__.' expects argument #2 to be a valid resource.');
        }
        $path = Util::normalizePath($path);
        $config = $this->prepareConfig($config);
        $this->assertPresent($path);
        Util::rewindStream($resource);
        return (bool) $this->adapter->updateStream($path, $resource, $config);
    }
    public function read($path)
    {
        $path = Util::normalizePath($path);
        $this->assertPresent($path);
        if (! ($object = $this->adapter->read($path))) {
            return false;
        }
        return $object['contents'];
    }
    public function readStream($path)
    {
        $path = Util::normalizePath($path);
        $this->assertPresent($path);
        if (! $object = $this->adapter->readStream($path)) {
            return false;
        }
        return $object['stream'];
    }
    public function rename($path, $newpath)
    {
        $path = Util::normalizePath($path);
        $newpath = Util::normalizePath($newpath);
        $this->assertPresent($path);
        $this->assertAbsent($newpath);
        return (bool) $this->adapter->rename($path, $newpath);
    }
    public function copy($path, $newpath)
    {
        $path = Util::normalizePath($path);
        $newpath = Util::normalizePath($newpath);
        $this->assertPresent($path);
        $this->assertAbsent($newpath);
        return $this->adapter->copy($path, $newpath);
    }
    public function delete($path)
    {
        $path = Util::normalizePath($path);
        $this->assertPresent($path);
        return $this->adapter->delete($path);
    }
    public function deleteDir($dirname)
    {
        $dirname = Util::normalizePath($dirname);
        if ($dirname === '') {
            throw new RootViolationException('Root directories can not be deleted.');
        }
        return (bool) $this->adapter->deleteDir($dirname);
    }
    public function createDir($dirname, array $config = [])
    {
        $dirname = Util::normalizePath($dirname);
        $config = $this->prepareConfig($config);
        return (bool) $this->adapter->createDir($dirname, $config);
    }
    public function listContents($directory = '', $recursive = false)
    {
        $directory = Util::normalizePath($directory);
        $contents = $this->adapter->listContents($directory, $recursive);
        $mapper = function ($entry) use ($directory, $recursive) {
            $entry = $entry + Util::pathinfo($entry['path']);
            if (! empty($directory) && strpos($entry['path'], $directory) === false) {
                return false;
            }
            if ($recursive === false && Util::dirname($entry['path']) !== $directory) {
                return false;
            }
            return $entry;
        };
        return array_values(array_filter(array_map($mapper, $contents)));
    }
    public function getMimetype($path)
    {
        $path = Util::normalizePath($path);
        $this->assertPresent($path);
        if (! $object = $this->adapter->getMimetype($path)) {
            return false;
        }
        return $object['mimetype'];
    }
    public function getTimestamp($path)
    {
        $path = Util::normalizePath($path);
        $this->assertPresent($path);
        if (! $object = $this->adapter->getTimestamp($path)) {
            return false;
        }
        return $object['timestamp'];
    }
    public function getVisibility($path)
    {
        $path = Util::normalizePath($path);
        $this->assertPresent($path);
        if (($object = $this->adapter->getVisibility($path)) === false) {
            return false;
        }
        return $object['visibility'];
    }
    public function getSize($path)
    {
        $path = Util::normalizePath($path);
        if (($object = $this->adapter->getSize($path)) === false || !isset($object['size'])) {
            return false;
        }
        return (int) $object['size'];
    }
    public function setVisibility($path, $visibility)
    {
        $path = Util::normalizePath($path);
        return (bool) $this->adapter->setVisibility($path, $visibility);
    }
    public function getMetadata($path)
    {
        $path = Util::normalizePath($path);
        $this->assertPresent($path);
        return $this->adapter->getMetadata($path);
    }
    public function get($path, Handler $handler = null)
    {
        $path = Util::normalizePath($path);
        if (! $handler) {
            $metadata = $this->getMetadata($path);
            $handler = $metadata['type'] === 'file' ? new File($this, $path) : new Directory($this, $path);
        }
        $handler->setPath($path);
        $handler->setFilesystem($this);
        return $handler;
    }
    protected function prepareConfig(array $config)
    {
        $config = new Config($config);
        $config->setFallback($this->config);
        return $config;
    }
    public function assertPresent($path)
    {
        if (! $this->has($path)) {
            throw new FileNotFoundException($path);
        }
    }
    public function assertAbsent($path)
    {
        if ($this->has($path)) {
            throw new FileExistsException($path);
        }
    }
    public function __call($method, array $arguments)
    {
        try {
            return $this->invokePlugin($method, $arguments, $this);
        } catch (PluginNotFoundException $e) {
            throw new BadMethodCallException(
                'Call to undefined method '
                .__CLASS__
                .'::'.$method
            );
        }
    }
}
