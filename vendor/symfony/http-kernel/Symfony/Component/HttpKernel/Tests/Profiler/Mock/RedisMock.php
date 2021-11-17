<?php
namespace Symfony\Component\HttpKernel\Tests\Profiler\Mock;
class RedisMock
{
    private $connected = false;
    private $storage = array();
    public function connect($host, $port = 6379, $timeout = 0)
    {
        if ('127.0.0.1' == $host && 6379 == $port) {
            $this->connected = true;
            return true;
        }
        return false;
    }
    public function setOption($name, $value)
    {
        if (!$this->connected) {
            return false;
        }
        return true;
    }
    public function exists($key)
    {
        if (!$this->connected) {
            return false;
        }
        return isset($this->storage[$key]);
    }
    public function setex($key, $ttl, $value)
    {
        if (!$this->connected) {
            return false;
        }
        $this->storeData($key, $value);
        return true;
    }
    public function setTimeout($key, $ttl)
    {
        if (!$this->connected) {
            return false;
        }
        if (isset($this->storage[$key])) {
            return true;
        }
        return false;
    }
    public function get($key)
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
            return strlen($this->storage[$key]);
        }
        return false;
    }
    public function delete($key)
    {
        if (!$this->connected) {
            return false;
        }
        if (is_array($key)) {
            $result = 0;
            foreach ($key as $k) {
                if (isset($this->storage[$k])) {
                    unset($this->storage[$k]);
                    ++$result;
                }
            }
            return $result;
        }
        if (isset($this->storage[$key])) {
            unset($this->storage[$key]);
            return 1;
        }
        return 0;
    }
    public function flushAll()
    {
        if (!$this->connected) {
            return false;
        }
        $this->storage = array();
        return true;
    }
    public function close()
    {
        $this->connected = false;
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
    public function select($dbnum)
    {
        if (!$this->connected) {
            return false;
        }
        if (0 > $dbnum) {
            return false;
        }
        return true;
    }
}
