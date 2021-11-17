<?php
namespace Symfony\Component\HttpKernel\Profiler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollectorInterface;
use Symfony\Component\HttpKernel\DataCollector\LateDataCollectorInterface;
use Psr\Log\LoggerInterface;
class Profiler
{
    private $storage;
    private $collectors = array();
    private $logger;
    private $enabled = true;
    public function __construct(ProfilerStorageInterface $storage, LoggerInterface $logger = null)
    {
        $this->storage = $storage;
        $this->logger = $logger;
    }
    public function disable()
    {
        $this->enabled = false;
    }
    public function enable()
    {
        $this->enabled = true;
    }
    public function loadProfileFromResponse(Response $response)
    {
        if (!$token = $response->headers->get('X-Debug-Token')) {
            return false;
        }
        return $this->loadProfile($token);
    }
    public function loadProfile($token)
    {
        return $this->storage->read($token);
    }
    public function saveProfile(Profile $profile)
    {
        foreach ($profile->getCollectors() as $collector) {
            if ($collector instanceof LateDataCollectorInterface) {
                $collector->lateCollect();
            }
        }
        if (!($ret = $this->storage->write($profile)) && null !== $this->logger) {
            $this->logger->warning('Unable to store the profiler information.');
        }
        return $ret;
    }
    public function purge()
    {
        $this->storage->purge();
    }
    public function export(Profile $profile)
    {
        return base64_encode(serialize($profile));
    }
    public function import($data)
    {
        $profile = unserialize(base64_decode($data));
        if ($this->storage->read($profile->getToken())) {
            return false;
        }
        $this->saveProfile($profile);
        return $profile;
    }
    public function find($ip, $url, $limit, $method, $start, $end)
    {
        return $this->storage->find($ip, $url, $limit, $method, $this->getTimestamp($start), $this->getTimestamp($end));
    }
    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        if (false === $this->enabled) {
            return;
        }
        $profile = new Profile(substr(hash('sha256', uniqid(mt_rand(), true)), 0, 6));
        $profile->setTime(time());
        $profile->setUrl($request->getUri());
        $profile->setIp($request->getClientIp());
        $profile->setMethod($request->getMethod());
        $response->headers->set('X-Debug-Token', $profile->getToken());
        foreach ($this->collectors as $collector) {
            $collector->collect($request, $response, $exception);
            $profile->addCollector(clone $collector);
        }
        return $profile;
    }
    public function all()
    {
        return $this->collectors;
    }
    public function set(array $collectors = array())
    {
        $this->collectors = array();
        foreach ($collectors as $collector) {
            $this->add($collector);
        }
    }
    public function add(DataCollectorInterface $collector)
    {
        $this->collectors[$collector->getName()] = $collector;
    }
    public function has($name)
    {
        return isset($this->collectors[$name]);
    }
    public function get($name)
    {
        if (!isset($this->collectors[$name])) {
            throw new \InvalidArgumentException(sprintf('Collector "%s" does not exist.', $name));
        }
        return $this->collectors[$name];
    }
    private function getTimestamp($value)
    {
        if (null === $value || '' == $value) {
            return;
        }
        try {
            $value = new \DateTime(is_numeric($value) ? '@'.$value : $value);
        } catch (\Exception $e) {
            return;
        }
        return $value->getTimestamp();
    }
}
