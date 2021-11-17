<?php
namespace League\Flysystem\Adapter;
use DirectoryIterator;
use FilesystemIterator;
use Finfo;
use League\Flysystem\AdapterInterface;
use League\Flysystem\Config;
use League\Flysystem\Util;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
class Local extends AbstractAdapter
{
    protected static $permissions = [
        'public' => 0744,
        'private' => 0700,
    ];
    protected $pathSeparator = DIRECTORY_SEPARATOR;
    public function __construct($root)
    {
        $realRoot = $this->ensureDirectory($root);
        if ( ! is_dir($realRoot) || ! is_readable($realRoot)) {
            throw new \LogicException('The root path '.$root.' is not readable.');
        }
        $this->setPathPrefix($realRoot);
    }
    protected function ensureDirectory($root)
    {
        if (is_dir($root) === false) {
            mkdir($root, 0755, true);
        }
        return realpath($root);
    }
    public function has($path)
    {
        $location = $this->applyPathPrefix($path);
        return file_exists($location);
    }
    public function write($path, $contents, Config $config)
    {
        $location = $this->applyPathPrefix($path);
        $this->ensureDirectory(dirname($location));
        if (($size = file_put_contents($location, $contents, LOCK_EX)) === false) {
            return false;
        }
        $type = 'file';
        $result = compact('contents', 'type', 'size', 'path');
        if ($visibility = $config->get('visibility')) {
            $result['visibility'] = $visibility;
            $this->setVisibility($path, $visibility);
        }
        return $result;
    }
    public function writeStream($path, $resource, Config $config)
    {
        $location = $this->applyPathPrefix($path);
        $this->ensureDirectory(dirname($location));
        if (! $stream = fopen($location, 'w+')) {
            return false;
        }
        while (! feof($resource)) {
            fwrite($stream, fread($resource, 1024), 1024);
        }
        if (! fclose($stream)) {
            return false;
        }
        if ($visibility = $config->get('visibility')) {
            $this->setVisibility($path, $visibility);
        }
        return compact('path', 'visibility');
    }
    public function readStream($path)
    {
        $location = $this->applyPathPrefix($path);
        $stream = fopen($location, 'r');
        return compact('stream', 'path');
    }
    public function updateStream($path, $resource, Config $config)
    {
        return $this->writeStream($path, $resource, $config);
    }
    public function update($path, $contents, Config $config)
    {
        $location = $this->applyPathPrefix($path);
        $mimetype = Util::guessMimeType($path, $contents);
        if (($size = file_put_contents($location, $contents, LOCK_EX)) === false) {
            return false;
        }
        return compact('path', 'size', 'contents', 'mimetype');
    }
    public function read($path)
    {
        $location = $this->applyPathPrefix($path);
        $contents = file_get_contents($location);
        if ($contents === false) {
            return false;
        }
        return compact('contents', 'path');
    }
    public function rename($path, $newpath)
    {
        $location = $this->applyPathPrefix($path);
        $destination = $this->applyPathPrefix($newpath);
        $parentDirectory = $this->applyPathPrefix(Util::dirname($newpath));
        $this->ensureDirectory($parentDirectory);
        return rename($location, $destination);
    }
    public function copy($path, $newpath)
    {
        $location = $this->applyPathPrefix($path);
        $destination = $this->applyPathPrefix($newpath);
        $this->ensureDirectory(dirname($destination));
        return copy($location, $destination);
    }
    public function delete($path)
    {
        $location = $this->applyPathPrefix($path);
        return unlink($location);
    }
    public function listContents($directory = '', $recursive = false)
    {
        $result = [];
        $location = $this->applyPathPrefix($directory).$this->pathSeparator;
        if (! is_dir($location)) {
            return [];
        }
        $iterator = $recursive ? $this->getRecursiveDirectoryIterator($location) : $this->getDirectoryIterator($location);
        foreach ($iterator as $file) {
            $path = $this->getFilePath($file);
            if (preg_match('#(^|/|\\\\)\.{1,2}$#', $path)) {
                continue;
            }
            $result[] = $this->normalizeFileInfo($file);
        }
        return $result;
    }
    public function getMetadata($path)
    {
        $location = $this->applyPathPrefix($path);
        $info = new SplFileInfo($location);
        return $this->normalizeFileInfo($info);
    }
    public function getSize($path)
    {
        return $this->getMetadata($path);
    }
    public function getMimetype($path)
    {
        $location = $this->applyPathPrefix($path);
        $finfo = new Finfo(FILEINFO_MIME_TYPE);
        return ['mimetype' => $finfo->file($location)];
    }
    public function getTimestamp($path)
    {
        return $this->getMetadata($path);
    }
    public function getVisibility($path)
    {
        $location = $this->applyPathPrefix($path);
        clearstatcache(false, $location);
        $permissions = octdec(substr(sprintf('%o', fileperms($location)), -4));
        $visibility = $permissions & 0044 ? AdapterInterface::VISIBILITY_PUBLIC : AdapterInterface::VISIBILITY_PRIVATE;
        return compact('visibility');
    }
    public function setVisibility($path, $visibility)
    {
        $location = $this->applyPathPrefix($path);
        chmod($location, static::$permissions[$visibility]);
        return compact('visibility');
    }
    public function createDir($dirname, Config $config)
    {
        $location = $this->applyPathPrefix($dirname);
        if (! is_dir($location) && ! mkdir($location, 0777, true)) {
            return false;
        }
        return ['path' => $dirname, 'type' => 'dir'];
    }
    public function deleteDir($dirname)
    {
        $location = $this->applyPathPrefix($dirname);
        if (! is_dir($location)) {
            return false;
        }
        $contents = $this->listContents($dirname, true);
        $contents = array_reverse($contents);
        foreach ($contents as $file) {
            if ($file['type'] === 'file') {
                unlink($this->applyPathPrefix($file['path']));
            } else {
                rmdir($this->applyPathPrefix($file['path']));
            }
        }
        return rmdir($location);
    }
    protected function normalizeFileInfo(SplFileInfo $file)
    {
        $normalized = [
            'type' => $file->getType(),
            'path' => $this->getFilePath($file),
            'timestamp' => $file->getMTime(),
        ];
        if ($normalized['type'] === 'file') {
            $normalized['size'] = $file->getSize();
        }
        return $normalized;
    }
    protected function getFilePath(SplFileInfo $file)
    {
        $path = $file->getPathname();
        $path = $this->removePathPrefix($path);
        return trim($path, '\\/');
    }
    protected function getRecursiveDirectoryIterator($path)
    {
        $directory = new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS);
        $iterator = new RecursiveIteratorIterator($directory, RecursiveIteratorIterator::SELF_FIRST);
        return $iterator;
    }
    protected function getDirectoryIterator($path)
    {
        $iterator = new DirectoryIterator($path);
        return $iterator;
    }
}
