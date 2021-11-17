<?php
namespace Symfony\Component\Security\Core\Authorization;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;
class AuthorizationChecker implements AuthorizationCheckerInterface
{
    private $tokenStorage;
    private $accessDecisionManager;
    private $authenticationManager;
    private $alwaysAuthenticate;
    public function __construct(TokenStorageInterface $tokenStorage, AuthenticationManagerInterface $authenticationManager, AccessDecisionManagerInterface $accessDecisionManager, $alwaysAuthenticate = false)
    {
        $this->tokenStorage = $tokenStorage;
        $this->authenticationManager = $authenticationManager;
        $this->accessDecisionManager = $accessDecisionManager;
        $this->alwaysAuthenticate = $alwaysAuthenticate;
    }
    final public function isGranted($attributes, $object = null)
    {
        if (null === ($token = $this->tokenStorage->getToken())) {
            throw new AuthenticationCredentialsNotFoundException('The token storage contains no authentication token. One possible reason may be that there is no firewall configured for this URL.');
        }
        if ($this->alwaysAuthenticate || !$token->isAuthenticated()) {
            $this->tokenStorage->setToken($token = $this->authenticationManager->authenticate($token));
        }
        if (!is_array($attributes)) {
            $attributes = array($attributes);
        }
        return $this->accessDecisionManager->decide($token, $attributes, $object);
    }
}
