<?php
namespace Symfony\Component\Security\Core\User;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
class ChainUserProvider implements UserProviderInterface
{
    private $providers;
    public function __construct(array $providers)
    {
        $this->providers = $providers;
    }
    public function getProviders()
    {
        return $this->providers;
    }
    public function loadUserByUsername($username)
    {
        foreach ($this->providers as $provider) {
            try {
                return $provider->loadUserByUsername($username);
            } catch (UsernameNotFoundException $notFound) {
            }
        }
        $ex = new UsernameNotFoundException(sprintf('There is no user with name "%s".', $username));
        $ex->setUsername($username);
        throw $ex;
    }
    public function refreshUser(UserInterface $user)
    {
        $supportedUserFound = false;
        foreach ($this->providers as $provider) {
            try {
                return $provider->refreshUser($user);
            } catch (UnsupportedUserException $unsupported) {
            } catch (UsernameNotFoundException $notFound) {
                $supportedUserFound = true;
            }
        }
        if ($supportedUserFound) {
            $ex = new UsernameNotFoundException(sprintf('There is no user with name "%s".', $user->getUsername()));
            $ex->setUsername($user->getUsername());
            throw $ex;
        } else {
            throw new UnsupportedUserException(sprintf('The account "%s" is not supported.', get_class($user)));
        }
    }
    public function supportsClass($class)
    {
        foreach ($this->providers as $provider) {
            if ($provider->supportsClass($class)) {
                return true;
            }
        }
        return false;
    }
}
