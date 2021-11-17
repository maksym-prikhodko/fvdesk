<?php
namespace Symfony\Component\Security\Core\Authentication\Token;
use Symfony\Component\Security\Core\User\UserInterface;
class RememberMeToken extends AbstractToken
{
    private $key;
    private $providerKey;
    public function __construct(UserInterface $user, $providerKey, $key)
    {
        parent::__construct($user->getRoles());
        if (empty($key)) {
            throw new \InvalidArgumentException('$key must not be empty.');
        }
        if (empty($providerKey)) {
            throw new \InvalidArgumentException('$providerKey must not be empty.');
        }
        $this->providerKey = $providerKey;
        $this->key = $key;
        $this->setUser($user);
        parent::setAuthenticated(true);
    }
    public function setAuthenticated($authenticated)
    {
        if ($authenticated) {
            throw new \LogicException('You cannot set this token to authenticated after creation.');
        }
        parent::setAuthenticated(false);
    }
    public function getProviderKey()
    {
        return $this->providerKey;
    }
    public function getKey()
    {
        return $this->key;
    }
    public function getCredentials()
    {
        return '';
    }
    public function serialize()
    {
        return serialize(array(
            $this->key,
            $this->providerKey,
            parent::serialize(),
        ));
    }
    public function unserialize($serialized)
    {
        list($this->key, $this->providerKey, $parentStr) = unserialize($serialized);
        parent::unserialize($parentStr);
    }
}
