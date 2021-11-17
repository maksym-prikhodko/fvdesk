<?php
namespace Symfony\Component\HttpFoundation\Session\Storage\Proxy;
abstract class AbstractProxy
{
    protected $wrapper = false;
    protected $active = false;
    protected $saveHandlerName;
    public function getSaveHandlerName()
    {
        return $this->saveHandlerName;
    }
    public function isSessionHandlerInterface()
    {
        return ($this instanceof \SessionHandlerInterface);
    }
    public function isWrapper()
    {
        return $this->wrapper;
    }
    public function isActive()
    {
        if (PHP_VERSION_ID >= 50400) {
            return $this->active = \PHP_SESSION_ACTIVE === session_status();
        }
        return $this->active;
    }
    public function setActive($flag)
    {
        if (PHP_VERSION_ID >= 50400) {
            throw new \LogicException('This method is disabled in PHP 5.4.0+');
        }
        $this->active = (bool) $flag;
    }
    public function getId()
    {
        return session_id();
    }
    public function setId($id)
    {
        if ($this->isActive()) {
            throw new \LogicException('Cannot change the ID of an active session');
        }
        session_id($id);
    }
    public function getName()
    {
        return session_name();
    }
    public function setName($name)
    {
        if ($this->isActive()) {
            throw new \LogicException('Cannot change the name of an active session');
        }
        session_name($name);
    }
}
