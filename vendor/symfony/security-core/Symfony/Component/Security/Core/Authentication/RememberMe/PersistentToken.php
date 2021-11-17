<?php
namespace Symfony\Component\Security\Core\Authentication\RememberMe;
final class PersistentToken implements PersistentTokenInterface
{
    private $class;
    private $username;
    private $series;
    private $tokenValue;
    private $lastUsed;
    public function __construct($class, $username, $series, $tokenValue, \DateTime $lastUsed)
    {
        if (empty($class)) {
            throw new \InvalidArgumentException('$class must not be empty.');
        }
        if (empty($username)) {
            throw new \InvalidArgumentException('$username must not be empty.');
        }
        if (empty($series)) {
            throw new \InvalidArgumentException('$series must not be empty.');
        }
        if (empty($tokenValue)) {
            throw new \InvalidArgumentException('$tokenValue must not be empty.');
        }
        $this->class = $class;
        $this->username = $username;
        $this->series = $series;
        $this->tokenValue = $tokenValue;
        $this->lastUsed = $lastUsed;
    }
    public function getClass()
    {
        return $this->class;
    }
    public function getUsername()
    {
        return $this->username;
    }
    public function getSeries()
    {
        return $this->series;
    }
    public function getTokenValue()
    {
        return $this->tokenValue;
    }
    public function getLastUsed()
    {
        return $this->lastUsed;
    }
}
