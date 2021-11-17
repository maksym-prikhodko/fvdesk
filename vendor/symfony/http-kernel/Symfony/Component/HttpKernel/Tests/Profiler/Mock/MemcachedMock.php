<?php
namespace Symfony\Component\HttpKernel\Tests\Profiler\Mock;
class MemcachedMock
{
    private $connected = false;
    private $storage = array();
    public function setOption($option, $value)
    {
        return true;
    }
    public function addServer($host, $port = 11211, $weight = 0)
    {
        if ('127.0.0.1' == $host && 11211 == $port) {
            $this->connected = true;
            return true;
        }
        return false;
    }
    public function add($key, $value, $expiration = 0)
    {
        if (!$this->connected) {
            return false;
        }
        if (!isset($this->storage[$key])) {
            $this->storeData($key, $value);
            return true;
        }
        return false;
    }
    public function set($key, $value, $expiration = null)
    {
        if (!$this->connected) {
            return false;
        }
        $this->storeData($key, $value);
        return true;
    }
    public function replace($key, $value, $expiration = null)
    {
        if (!$this->connected) {
            return false;
        }
        if (isset($this->storage[$key])) {
            $this->storeData($key, $value);
            return true;
        }
        return false;
    }
    public function get($key, $cache_cb = null, &$cas_token = null)
    {
        if (!$this->connected) {
            return false;
        }
        return $this->getData($key);
    }
    public function append($key, $value)
    {
        if (!$this->connected) {
            return false;
        }
        if (isset($this->storage[$key])) {
            $this->storeData($key, $this->getData($key).$value);
            return true;
        }
        return false;
    }
    public function delete($key)
    {
        if (!$this->connected) {
            return false;
        }
        if (isset($this->storage[$key])) {
            unset($this->storage[$key]);
            return true;
        }
        return false;
    }
    public function flush()
    {
        if (!$this->connected) {
            return false;
        }
        $this->storage = array();
        return true;
    }
    private function getData($key)
    {
        if (isset($this->storage[$key])) {
            return unserialize($this->storage[$key]);
        }
        return false;
    }
    private function storeData($key, $value)
    {
        $this->storage[$key] = serialize($value);
        return true;
    }
}
