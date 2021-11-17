<?php
namespace Symfony\Component\Security\Core\Authentication\Provider;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
class PreAuthenticatedAuthenticationProvider implements AuthenticationProviderInterface
{
    private $userProvider;
    private $userChecker;
    private $providerKey;
    public function __construct(UserProviderInterface $userProvider, UserCheckerInterface $userChecker, $providerKey)
    {
        $this->userProvider = $userProvider;
        $this->userChecker = $userChecker;
        $this->providerKey = $providerKey;
    }
    public function authenticate(TokenInterface $token)
    {
        if (!$this->supports($token)) {
            return;
        }
        if (!$user = $token->getUser()) {
            throw new BadCredentialsException('No pre-authenticated principal found in request.');
        }
         $user = $this->userProvider->loadUserByUsername($user);
        $this->userChecker->checkPostAuth($user);
        $authenticatedToken = new PreAuthenticatedToken($user, $token->getCredentials(), $this->providerKey, $user->getRoles());
        $authenticatedToken->setAttributes($token->getAttributes());
        return $authenticatedToken;
    }
    public function supports(TokenInterface $token)
    {
        return $token instanceof PreAuthenticatedToken && $this->providerKey === $token->getProviderKey();
    }
}
