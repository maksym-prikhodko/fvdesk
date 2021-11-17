<?php
namespace Symfony\Component\Security\Core\Authentication\Provider;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
class AnonymousAuthenticationProvider implements AuthenticationProviderInterface
{
    private $key;
    public function __construct($key)
    {
        $this->key = $key;
    }
    public function authenticate(TokenInterface $token)
    {
        if (!$this->supports($token)) {
            return;
        }
        if ($this->key !== $token->getKey()) {
            throw new BadCredentialsException('The Token does not contain the expected key.');
        }
        return $token;
    }
    public function supports(TokenInterface $token)
    {
        return $token instanceof AnonymousToken;
    }
}
