<?php
namespace Symfony\Component\Security\Core\Authentication\Token;
use Symfony\Component\Security\Core\Role\RoleInterface;
class AnonymousToken extends AbstractToken
{
    private $key;
    public function __construct($key, $user, array $roles = array())
    {
        parent::__construct($roles);
        $this->key = $key;
        $this->setUser($user);
        $this->setAuthenticated(true);
    }
    public function getCredentials()
    {
        return '';
    }
    public function getKey()
    {
        return $this->key;
    }
    public function serialize()
    {
        return serialize(array($this->key, parent::serialize()));
    }
    public function unserialize($serialized)
    {
        list($this->key, $parentStr) = unserialize($serialized);
        parent::unserialize($parentStr);
    }
}
