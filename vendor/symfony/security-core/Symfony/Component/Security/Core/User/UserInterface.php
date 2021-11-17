<?php
namespace Symfony\Component\Security\Core\User;
use Symfony\Component\Security\Core\Role\Role;
interface UserInterface
{
    public function getRoles();
    public function getPassword();
    public function getSalt();
    public function getUsername();
    public function eraseCredentials();
}
