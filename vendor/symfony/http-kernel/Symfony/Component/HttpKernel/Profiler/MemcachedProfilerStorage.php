<?php
namespace Symfony\Component\HttpKernel\Profiler;
class MemcachedProfilerStorage extends BaseMemcacheProfilerStorage
{
    private $memcached;
    protected function getMemcached()
    {
        if (null === $this->memcached) {
            if (!preg_match('#^memcached:
                throw new \RuntimeException(sprintf('Please check your configuration. You are trying to use Memcached with an invalid dsn "%s". The expected format is "memcached:
            }
            $host = $matches[1] ?: $matches[2];
            $port = $matches[3];
            $memcached = new \Memcached();
            $memcached->setOption(\Memcached::OPT_COMPRESSION, false);
            $memcached->addServer($host, $port);
            $this->memcached = $memcached;
        }
        return $this->memcached;
    }
    public function setMemcached($memcached)
    {
        $this->memcached = $memcached;
    }
    protected function getValue($key)
    {
        return $this->getMemcached()->get($key);
    }
    protected function setValue($key, $value, $expiration = 0)
    {
        return $this->getMemcached()->set($key, $value, time() + $expiration);
    }
    protected function delete($key)
    {
        return $this->getMemcached()->delete($key);
    }
    protected function appendValue($key, $value, $expiration = 0)
    {
        $memcached = $this->getMemcached();
        if (!$result = $memcached->append($key, $value)) {
            return $memcached->set($key, $value, $expiration);
        }
        return $result;
    }
}
