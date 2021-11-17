<?php
namespace Symfony\Component\HttpKernel\Profiler;
class MemcacheProfilerStorage extends BaseMemcacheProfilerStorage
{
    private $memcache;
    protected function getMemcache()
    {
        if (null === $this->memcache) {
            if (!preg_match('#^memcache:
                throw new \RuntimeException(sprintf('Please check your configuration. You are trying to use Memcache with an invalid dsn "%s". The expected format is "memcache:
            }
            $host = $matches[1] ?: $matches[2];
            $port = $matches[3];
            $memcache = new \Memcache();
            $memcache->addServer($host, $port);
            $this->memcache = $memcache;
        }
        return $this->memcache;
    }
    public function setMemcache($memcache)
    {
        $this->memcache = $memcache;
    }
    protected function getValue($key)
    {
        return $this->getMemcache()->get($key);
    }
    protected function setValue($key, $value, $expiration = 0)
    {
        return $this->getMemcache()->set($key, $value, false, time() + $expiration);
    }
    protected function delete($key)
    {
        return $this->getMemcache()->delete($key);
    }
    protected function appendValue($key, $value, $expiration = 0)
    {
        $memcache = $this->getMemcache();
        if (method_exists($memcache, 'append')) {
            if (!$result = $memcache->append($key, $value, false, $expiration)) {
                return $memcache->set($key, $value, false, $expiration);
            }
            return $result;
        }
        $content = $memcache->get($key);
        return $memcache->set($key, $content.$value, false, $expiration);
    }
}
