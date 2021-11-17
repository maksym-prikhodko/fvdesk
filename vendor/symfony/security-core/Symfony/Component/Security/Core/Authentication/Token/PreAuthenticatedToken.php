<?php
namespace Symfony\Component\Security\Core\Authentication\Token;
class PreAuthenticatedToken extends AbstractToken
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
        if ($roles) {
            $this->setAuthenticated(true);
        }
    }
    public function getProviderKey()
    {
        return $this->providerKey;
    }
    public function getCredentials()
    {
        return $this->credentials;
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
    public function unserialize($str)
    {
        list($this->credentials, $this->providerKey, $parentStr) = unserialize($str);
        parent::unserialize($parentStr);
    }
}
