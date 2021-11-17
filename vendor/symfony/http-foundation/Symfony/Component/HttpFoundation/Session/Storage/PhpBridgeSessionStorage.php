<?php
namespace Symfony\Component\HttpFoundation\Session\Storage;
use Symfony\Component\HttpFoundation\Session\Storage\Proxy\AbstractProxy;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\NativeSessionHandler;
class PhpBridgeSessionStorage extends NativeSessionStorage
{
    public function __construct($handler = null, MetadataBag $metaBag = null)
    {
        $this->setMetadataBag($metaBag);
        $this->setSaveHandler($handler);
    }
    public function start()
    {
        if ($this->started) {
            return true;
        }
        $this->loadSession();
        if (!$this->saveHandler->isWrapper() && !$this->saveHandler->isSessionHandlerInterface()) {
            $this->saveHandler->setActive(true);
        }
        return true;
    }
    public function clear()
    {
        foreach ($this->bags as $bag) {
            $bag->clear();
        }
        $this->loadSession();
    }
}
