<?php
namespace League\Flysystem;
use League\Flysystem\Util\MimeType;
use LogicException;
class Util
{
    public static function pathinfo($path)
    {
        $pathinfo = pathinfo($path) + compact('path');
        $pathinfo['dirname'] = static::normalizeDirname($pathinfo['dirname']);
        return $pathinfo;
    }
    public static function normalizeDirname($dirname)
    {
        if ($dirname === '.') {
            return '';
        }
        return $dirname;
    }
    public static function dirname($path)
    {
        return static::normalizeDirname(dirname($path));
    }
    public static function map(array $object, array $map)
    {
        $result = [];
        foreach ($map as $from => $to) {
            if (! isset($object[$from])) {
                continue;
            }
            $result[$to] = $object[$from];
        }
        return $result;
    }
    public static function normalizePath($path)
    {
        $normalized = preg_replace('#\p{C}+|^\./#u', '', $path);
        $normalized = static::normalizeRelativePath($normalized);
        if (preg_match('#/\.{2}|^\.{2}/|^\.{2}$#', $normalized)) {
            throw new LogicException('Path is outside of the defined root, path: ['.$path.'], resolved: ['.$normalized.']');
        }
        $normalized = preg_replace('#\\\{2,}#', '\\', trim($normalized, '\\'));
        $normalized = preg_replace('#/{2,}#', '/', trim($normalized, '/'));
        return $normalized;
    }
    public static function normalizeRelativePath($path)
    {
        $path = preg_replace('#/\.(?=/)|^\./|\./$#', '', $path);
        $regex = '#
    public static function normalizePrefix($prefix, $separator)
    {
        return rtrim($prefix, $separator).$separator;
    }
    public static function contentSize($contents)
    {
        return mb_strlen($contents, '8bit');
    }
    public static function guessMimeType($path, $content)
    {
        $mimeType = MimeType::detectByContent($content);
        if (empty($mimeType) || $mimeType === 'text/plain') {
            $extension = pathinfo($path, PATHINFO_EXTENSION);
            if ($extension) {
                $mimeType = MimeType::detectByFileExtension($extension) ?: 'text/plain';
            }
        }
        return $mimeType;
    }
    public static function emulateDirectories(array $listing)
    {
        $directories = [];
        $listedDirectories = [];
        foreach ($listing as $object) {
            list($directories, $listedDirectories) = static::emulateObjectDirectories($object, $directories, $listedDirectories);
        }
        $directories = array_diff(array_unique($directories), array_unique($listedDirectories));
        foreach ($directories as $directory) {
            $listing[] = static::pathinfo($directory) + ['type' => 'dir'];
        }
        return $listing;
    }
    public static function ensureConfig($config)
    {
        if ($config === null) {
            return new Config();
        }
        if ($config instanceof Config) {
            return $config;
        }
        if (is_array($config)) {
            return new Config($config);
        }
        throw new LogicException('A config should either be an array or a Flysystem\Config object.');
    }
    public static function rewindStream($resource)
    {
        if (ftell($resource) !== 0 && static::isSeekableStream($resource)) {
            rewind($resource);
        }
    }
    public static function isSeekableStream($resource)
    {
        $metadata = stream_get_meta_data($resource);
        return $metadata['seekable'];
    }
    public static function getStreamSize($resource)
    {
        $stat = fstat($resource);
        return $stat['size'];
    }
    protected static function emulateObjectDirectories(array $object, array $directories, array $listedDirectories)
    {
        if (empty($object['dirname'])) {
            return [$directories, $listedDirectories];
        }
        $parent = $object['dirname'];
        while (! empty($parent) && ! in_array($parent, $directories)) {
            $directories[] = $parent;
            $parent = static::dirname($parent);
        }
        if (isset($object['type']) && $object['type'] === 'dir') {
            $listedDirectories[] = $object['path'];
            return [$directories, $listedDirectories];
        }
        return [$directories, $listedDirectories];
    }
}
