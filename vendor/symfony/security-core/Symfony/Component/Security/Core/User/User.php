<?php
namespace Symfony\Component\Security\Core\User;
final class User implements AdvancedUserInterface
{
    private $username;
    private $password;
    private $enabled;
    private $accountNonExpired;
    private $credentialsNonExpired;
    private $accountNonLocked;
    private $roles;
    public function __construct($username, $password, array $roles = array(), $enabled = true, $userNonExpired = true, $credentialsNonExpired = true, $userNonLocked = true)
    {
        if (empty($username)) {
            throw new \InvalidArgumentException('The username cannot be empty.');
        }
        $this->username = $username;
        $this->password = $password;
        $this->enabled = $enabled;
        $this->accountNonExpired = $userNonExpired;
        $this->credentialsNonExpired = $credentialsNonExpired;
        $this->accountNonLocked = $userNonLocked;
        $this->roles = $roles;
    }
    public function getRoles()
    {
        return $this->roles;
    }
    public function getPassword()
    {
        return $this->password;
    }
    public function getSalt()
    {
    }
    public function getUsername()
    {
        return $this->username;
    }
    public function isAccountNonExpired()
    {
        return $this->accountNonExpired;
    }
    public function isAccountNonLocked()
    {
        return $this->accountNonLocked;
    }
    public function isCredentialsNonExpired()
    {
        return $this->credentialsNonExpired;
    }
    public function isEnabled()
    {
        return $this->enabled;
    }
    public function eraseCredentials()
    {
    }
}
