<?php
namespace Symfony\Component\Security\Core\Authentication;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
class AuthenticationTrustResolver implements AuthenticationTrustResolverInterface
{
    private $anonymousClass;
    private $rememberMeClass;
    public function __construct($anonymousClass, $rememberMeClass)
    {
        $this->anonymousClass = $anonymousClass;
        $this->rememberMeClass = $rememberMeClass;
    }
    public function isAnonymous(TokenInterface $token = null)
    {
        if (null === $token) {
            return false;
        }
        return $token instanceof $this->anonymousClass;
    }
    public function isRememberMe(TokenInterface $token = null)
    {
        if (null === $token) {
            return false;
        }
        return $token instanceof $this->rememberMeClass;
    }
    public function isFullFledged(TokenInterface $token = null)
    {
        if (null === $token) {
            return false;
        }
        return !$this->isAnonymous($token) && !$this->isRememberMe($token);
    }
}
