<?php
namespace Symfony\Component\HttpKernel\CacheWarmer;
class CacheWarmerAggregate implements CacheWarmerInterface
{
    protected $warmers = array();
    protected $optionalsEnabled = false;
    public function __construct(array $warmers = array())
    {
        foreach ($warmers as $warmer) {
            $this->add($warmer);
        }
    }
    public function enableOptionalWarmers()
    {
        $this->optionalsEnabled = true;
    }
    public function warmUp($cacheDir)
    {
        foreach ($this->warmers as $warmer) {
            if (!$this->optionalsEnabled && $warmer->isOptional()) {
                continue;
            }
            $warmer->warmUp($cacheDir);
        }
    }
    public function isOptional()
    {
        return false;
    }
    public function setWarmers(array $warmers)
    {
        $this->warmers = array();
        foreach ($warmers as $warmer) {
            $this->add($warmer);
        }
    }
    public function add(CacheWarmerInterface $warmer)
    {
        $this->warmers[] = $warmer;
    }
}
