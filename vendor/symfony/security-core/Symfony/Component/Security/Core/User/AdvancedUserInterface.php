<?php
namespace Symfony\Component\Security\Core\User;
use Symfony\Component\Security\Core\Exception\AccountStatusException;
use Symfony\Component\Security\Core\Exception\AccountExpiredException;
use Symfony\Component\Security\Core\Exception\LockedException;
use Symfony\Component\Security\Core\Exception\CredentialsExpiredException;
use Symfony\Component\Security\Core\Exception\DisabledException;
interface AdvancedUserInterface extends UserInterface
{
    public function isAccountNonExpired();
    public function isAccountNonLocked();
    public function isCredentialsNonExpired();
    public function isEnabled();
}
