<?php
namespace Symfony\Component\Security\Core\Authentication\Provider;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
interface AuthenticationProviderInterface extends AuthenticationManagerInterface
{
    public function supports(TokenInterface $token);
}
