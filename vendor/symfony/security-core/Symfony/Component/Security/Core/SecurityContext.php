<?php
namespace Symfony\Component\Security\Core;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
class SecurityContext implements SecurityContextInterface
{
    private $tokenStorage;
    private $authorizationChecker;
    public function __construct($tokenStorage, $authorizationChecker, $alwaysAuthenticate = false)
    {
        $oldSignature = $tokenStorage instanceof AuthenticationManagerInterface && $authorizationChecker instanceof AccessDecisionManagerInterface;
        $newSignature = $tokenStorage instanceof TokenStorageInterface && $authorizationChecker instanceof AuthorizationCheckerInterface;
        if (!$oldSignature && !$newSignature) {
            throw new \BadMethodCallException('Unable to construct SecurityContext, please provide the correct arguments');
        }
        if ($oldSignature) {
            $authenticationManager = $tokenStorage;
            $accessDecisionManager = $authorizationChecker;
            $tokenStorage = new TokenStorage();
            $authorizationChecker = new AuthorizationChecker($tokenStorage, $authenticationManager, $accessDecisionManager, $alwaysAuthenticate);
        }
        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;
    }
    public function getToken()
    {
        return $this->tokenStorage->getToken();
    }
    public function setToken(TokenInterface $token = null)
    {
        return $this->tokenStorage->setToken($token);
    }
    public function isGranted($attributes, $object = null)
    {
        return $this->authorizationChecker->isGranted($attributes, $object);
    }
}
