<?php
namespace Symfony\Component\Security\Core\Exception;
class UsernameNotFoundException extends AuthenticationException
{
    private $username;
    public function getMessageKey()
    {
        return 'Username could not be found.';
    }
    public function getUsername()
    {
        return $this->username;
    }
    public function setUsername($username)
    {
        $this->username = $username;
    }
    public function serialize()
    {
        return serialize(array(
            $this->username,
            parent::serialize(),
        ));
    }
    public function unserialize($str)
    {
        list($this->username, $parentData) = unserialize($str);
        parent::unserialize($parentData);
    }
    public function getMessageData()
    {
        return array('{{ username }}' => $this->username);
    }
}
