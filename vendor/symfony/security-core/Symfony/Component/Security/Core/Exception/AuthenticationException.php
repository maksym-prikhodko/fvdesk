<?php
namespace Symfony\Component\Security\Core\Exception;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
class AuthenticationException extends \RuntimeException implements \Serializable
{
    private $token;
    public function getToken()
    {
        return $this->token;
    }
    public function setToken(TokenInterface $token)
    {
        $this->token = $token;
    }
    public function serialize()
    {
        return serialize(array(
            $this->token,
            $this->code,
            $this->message,
            $this->file,
            $this->line,
        ));
    }
    public function unserialize($str)
    {
        list(
            $this->token,
            $this->code,
            $this->message,
            $this->file,
            $this->line
        ) = unserialize($str);
    }
    public function getMessageKey()
    {
        return 'An authentication exception occurred.';
    }
    public function getMessageData()
    {
        return array();
    }
}
