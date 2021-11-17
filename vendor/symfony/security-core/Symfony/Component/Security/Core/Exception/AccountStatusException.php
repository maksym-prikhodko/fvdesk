<?php
namespace Symfony\Component\Security\Core\Exception;
use Symfony\Component\Security\Core\User\UserInterface;
abstract class AccountStatusException extends AuthenticationException
{
    private $user;
    public function getUser()
    {
        return $this->user;
    }
    public function setUser(UserInterface $user)
    {
        $this->user = $user;
    }
    public function serialize()
    {
        return serialize(array(
            $this->user,
            parent::serialize(),
        ));
    }
    public function unserialize($str)
    {
        list($this->user, $parentData) = unserialize($str);
        parent::unserialize($parentData);
    }
}
