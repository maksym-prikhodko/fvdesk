<?php
namespace Symfony\Component\Security\Core\Authentication\Token\Storage;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
class TokenStorage implements TokenStorageInterface
{
    private $token;
    public function getToken()
    {
        return $this->token;
    }
    public function setToken(TokenInterface $token = null)
    {
        $this->token = $token;
    }
}
