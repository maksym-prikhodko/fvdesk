<?php
namespace Symfony\Component\Security\Core\Authentication;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
interface AuthenticationManagerInterface
{
    public function authenticate(TokenInterface $token);
}
