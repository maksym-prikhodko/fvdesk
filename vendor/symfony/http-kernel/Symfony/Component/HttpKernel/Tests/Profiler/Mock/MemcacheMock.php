<?php
namespace Symfony\Component\HttpKernel\Tests\Profiler\Mock;
class MemcacheMock
{
    private $connected = false;
    private $storage = array();
    public function connect($host, $port = null, $timeout = null)
    {
        if ('127.0.0.1' == $host && 11211 == $port) {
            $this->connected = true;
            return true;
        }
        return false;
    }
    public function pconnect($host, $port = null, $timeout = null)
    {
        if ('127.0.0.1' == $host && 11211 == $port) {
            $this->connected = true;
            return true;
        }
        return false;
    }
    public function addServer($host, $port = 11211, $persistent = null, $weight = null, $timeout = null, $retry_interval = null, $status = null, $failure_callback = null, $timeoutms = null)
    {
        if ('127.0.0.1' == $host && 11211 == $port) {
            $this->connected = true;
            return true;
        }
        return false;
    }
    public function add($key, $var, $flag = null, $expire = null)
    {
        if (!$this->connected) {
            return false;
        }
        if (!isset($this->storage[$key])) {
            $this->storeData($key, $var);
            return true;
        }
        return false;
    }
    public function set($key, $var, $flag = null, $expire = null)
    {
        if (!$this->connected) {
            return false;
        }
        $this->storeData($key, $var);
        return true;
    }
    public function replace($key, $var, $flag = null, $expire = null)
    {
        if (!$this->connected) {
            return false;
        }
        if (isset($this->storage[$key])) {
            $this->storeData($key, $var);
            return true;
        }
        return false;
    }
    public function get($key, &$flags = null)
    {
        if (!$this->connected) {
            return false;
        }
        if (is_array($key)) {
            $result = array();
            foreach ($key as $k) {
                if (isset($this->storage[$k])) {
                    $result[] = $this->getData($k);
                }
            }
            return $result;
        }
        return $this->getData($key);
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
}
