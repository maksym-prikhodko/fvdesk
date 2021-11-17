<?php
namespace Symfony\Component\Security\Core\User;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
class InMemoryUserProvider implements UserProviderInterface
{
    private $users;
    public function __construct(array $users = array())
    {
        foreach ($users as $username => $attributes) {
            $password = isset($attributes['password']) ? $attributes['password'] : null;
            $enabled = isset($attributes['enabled']) ? $attributes['enabled'] : true;
            $roles = isset($attributes['roles']) ? $attributes['roles'] : array();
            $user = new User($username, $password, $roles, $enabled, true, true, true);
            $this->createUser($user);
        }
    }
    public function createUser(UserInterface $user)
    {
        if (isset($this->users[strtolower($user->getUsername())])) {
            throw new \LogicException('Another user with the same username already exists.');
        }
        $this->users[strtolower($user->getUsername())] = $user;
    }
    public function loadUserByUsername($username)
    {
        if (!isset($this->users[strtolower($username)])) {
            $ex = new UsernameNotFoundException(sprintf('Username "%s" does not exist.', $username));
            $ex->setUsername($username);
            throw $ex;
        }
        $user = $this->users[strtolower($username)];
        return new User($user->getUsername(), $user->getPassword(), $user->getRoles(), $user->isEnabled(), $user->isAccountNonExpired(),
                $user->isCredentialsNonExpired(), $user->isAccountNonLocked());
    }
    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }
        return $this->loadUserByUsername($user->getUsername());
    }
    public function supportsClass($class)
    {
        return $class === 'Symfony\Component\Security\Core\User\User';
    }
}
