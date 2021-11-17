<?php
namespace Symfony\Component\Security\Core\Authentication;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
interface AuthenticationTrustResolverInterface
{
    public function isAnonymous(TokenInterface $token = null);
    public function isRememberMe(TokenInterface $token = null);
    public function isFullFledged(TokenInterface $token = null);
}
