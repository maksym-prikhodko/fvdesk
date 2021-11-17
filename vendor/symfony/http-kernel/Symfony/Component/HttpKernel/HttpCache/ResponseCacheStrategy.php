<?php
namespace Symfony\Component\HttpKernel\HttpCache;
use Symfony\Component\HttpFoundation\Response;
class ResponseCacheStrategy implements ResponseCacheStrategyInterface
{
    private $cacheable = true;
    private $embeddedResponses = 0;
    private $ttls = array();
    private $maxAges = array();
    public function add(Response $response)
    {
        if ($response->isValidateable()) {
            $this->cacheable = false;
        } else {
            $this->ttls[] = $response->getTtl();
            $this->maxAges[] = $response->getMaxAge();
        }
        $this->embeddedResponses++;
    }
    public function update(Response $response)
    {
        if (0 === $this->embeddedResponses) {
            return;
        }
        if ($response->isValidateable()) {
            $response->setEtag(null);
            $response->setLastModified(null);
            $this->cacheable = false;
        }
        if (!$this->cacheable) {
            $response->headers->set('Cache-Control', 'no-cache, must-revalidate');
            return;
        }
        $this->ttls[] = $response->getTtl();
        $this->maxAges[] = $response->getMaxAge();
        if (null !== $maxAge = min($this->maxAges)) {
            $response->setSharedMaxAge($maxAge);
            $response->headers->set('Age', $maxAge - min($this->ttls));
        }
        $response->setMaxAge(0);
    }
}
