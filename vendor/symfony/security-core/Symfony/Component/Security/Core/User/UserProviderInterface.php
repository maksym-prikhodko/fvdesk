<?php
namespace Symfony\Component\Security\Core\User;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
interface UserProviderInterface
{
    public function loadUserByUsername($username);
    public function refreshUser(UserInterface $user);
    public function supportsClass($class);
}
