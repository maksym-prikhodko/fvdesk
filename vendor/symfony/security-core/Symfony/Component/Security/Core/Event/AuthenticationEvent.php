<?php
namespace Symfony\Component\Security\Core\Event;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\EventDispatcher\Event;
class AuthenticationEvent extends Event
{
    private $authenticationToken;
    public function __construct(TokenInterface $token)
    {
        $this->authenticationToken = $token;
    }
    public function getAuthenticationToken()
    {
        return $this->authenticationToken;
    }
}
