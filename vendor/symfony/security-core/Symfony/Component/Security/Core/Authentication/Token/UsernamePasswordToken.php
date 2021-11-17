<?php
namespace Symfony\Component\Security\Core\Authentication\Token;
use Symfony\Component\Security\Core\Role\RoleInterface;
class UsernamePasswordToken extends AbstractToken
{
    private $credentials;
    private $providerKey;
    public function __construct($user, $credentials, $providerKey, array $roles = array())
    {
        parent::__construct($roles);
        if (empty($providerKey)) {
            throw new \InvalidArgumentException('$providerKey must not be empty.');
        }
        $this->setUser($user);
        $this->credentials = $credentials;
        $this->providerKey = $providerKey;
        parent::setAuthenticated(count($roles) > 0);
    }
    public function setAuthenticated($isAuthenticated)
    {
        if ($isAuthenticated) {
            throw new \LogicException('Cannot set this token to trusted after instantiation.');
        }
        parent::setAuthenticated(false);
    }
    public function getCredentials()
    {
        return $this->credentials;
    }
    public function getProviderKey()
    {
        return $this->providerKey;
    }
    public function eraseCredentials()
    {
        parent::eraseCredentials();
        $this->credentials = null;
    }
    public function serialize()
    {
        return serialize(array($this->credentials, $this->providerKey, parent::serialize()));
    }
    public function unserialize($serialized)
    {
        list($this->credentials, $this->providerKey, $parentStr) = unserialize($serialized);
        parent::unserialize($parentStr);
    }
}
