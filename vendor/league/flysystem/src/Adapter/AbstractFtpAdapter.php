<?php
namespace League\Flysystem\Adapter;
use League\Flysystem\AdapterInterface;
use League\Flysystem\Config;
use Net_SFTP;
abstract class AbstractFtpAdapter extends AbstractAdapter
{
    protected $connection;
    protected $host;
    protected $port = 21;
    protected $username;
    protected $password;
    protected $ssl = false;
    protected $timeout = 90;
    protected $passive = true;
    protected $separator = '/';
    protected $root;
    protected $permPublic = 0744;
    protected $permPrivate = 0700;
    protected $configurable = [];
    public function __construct(array $config)
    {
        $this->setConfig($config);
    }
    public function setConfig(array $config)
    {
        foreach ($this->configurable as $setting) {
            if (! isset($config[$setting])) {
                continue;
            }
            $this->{'set'.ucfirst($setting)}($config[$setting]);
        }
        return $this;
    }
    public function getHost()
    {
        return $this->host;
    }
    public function setHost($host)
    {
        $this->host = $host;
        return $this;
    }
    public function setPermPublic($permPublic)
    {
        $this->permPublic = $permPublic;
        return $this;
    }
    public function setPermPrivate($permPrivate)
    {
        $this->permPrivate = $permPrivate;
        return $this;
    }
    public function getPort()
    {
        return $this->port;
    }
    public function getRoot()
    {
        return $this->root;
    }
    public function setPort($port)
    {
        $this->port = (int) $port;
        return $this;
    }
    public function setRoot($root)
    {
        $this->root = rtrim($root, '\\/').$this->separator;
        return $this;
    }
    public function getUsername()
    {
        return empty($this->username) ? 'anonymous' : $this->username;
    }
    public function setUsername($username)
    {
        $this->username = $username;
        return $this;
    }
    public function getPassword()
    {
        return $this->password;
    }
    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }
    public function getTimeout()
    {
        return $this->timeout;
    }
    public function setTimeout($timeout)
    {
        $this->timeout = (int) $timeout;
        return $this;
    }
    public function listContents($directory = '', $recursive = false)
    {
        return $this->listDirectoryContents($directory, $recursive);
    }
    protected function normalizeListing(array $listing, $prefix = '')
    {
        $base = $prefix;
        $result = [];
        $listing = $this->removeDotDirectories($listing);
        while ($item = array_shift($listing)) {
            if (preg_match('#^.*:$#', $item)) {
                $base = trim($item, ':');
                continue;
            }
            $result[] = $this->normalizeObject($item, $base);
        }
        return $this->sortListing($result);
    }
    protected function sortListing(array $result)
    {
        $compare = function ($one, $two) {
            return strnatcmp($one['path'], $two['path']);
        };
        usort($result, $compare);
        return $result;
    }
    protected function normalizeObject($item, $base)
    {
        $item = preg_replace('#\s+#', ' ', trim($item), 7);
        list($permissions, , , , $size, , , , $name) = explode(' ', $item, 9);
        $type = $this->detectType($permissions);
        $path = empty($base) ? $name : $base.$this->separator.$name;
        if ($type === 'dir') {
            return compact('type', 'path');
        }
        $permissions = $this->normalizePermissions($permissions);
        $visibility = $permissions & 0044 ? AdapterInterface::VISIBILITY_PUBLIC : AdapterInterface::VISIBILITY_PRIVATE;
        $size = (int) $size;
        return compact('type', 'path', 'visibility', 'size');
    }
    protected function detectType($permissions)
    {
        return substr($permissions, 0, 1) === 'd' ? 'dir' : 'file';
    }
    protected function normalizePermissions($permissions)
    {
        $permissions = substr($permissions, 1);
        $map = ['-' => '0', 'r' => '4', 'w' => '2', 'x' => '1'];
        $permissions = strtr($permissions, $map);
        $parts = str_split($permissions, 3);
        $mapper = function ($part) {
            return array_sum(str_split($part));
        };
        return array_sum(array_map($mapper, $parts));
    }
    public function removeDotDirectories(array $list)
    {
        $filter = function ($line) {
            if (! empty($line) && ! preg_match('#.* \.(\.)?$|^total#', $line)) {
                return true;
            }
            return false;
        };
        return array_filter($list, $filter);
    }
    public function has($path)
    {
        return $this->getMetadata($path);
    }
    public function getSize($path)
    {
        return $this->getMetadata($path);
    }
    public function getTimestamp($path)
    {
        $timestamp = ftp_mdtm($this->getConnection(), $path);
        return ($timestamp !== -1) ? ['timestamp' => $timestamp] : false;
    }
    public function getVisibility($path)
    {
        return $this->getMetadata($path);
    }
    public function ensureDirectory($dirname)
    {
        if (! empty($dirname) && ! $this->has($dirname)) {
            $this->createDir($dirname, new Config());
        }
    }
    public function getConnection()
    {
        if (! $this->connection) {
            $this->connect();
        }
        return $this->connection;
    }
    public function getPermPublic()
    {
        return $this->permPublic;
    }
    public function getPermPrivate()
    {
        return $this->permPrivate;
    }
    public function __destruct()
    {
        $this->disconnect();
    }
    abstract public function connect();
    abstract public function disconnect();
}
