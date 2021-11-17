<?php
namespace Symfony\Component\Security\Core\Role;
class Role implements RoleInterface
{
    private $role;
    public function __construct($role)
    {
        $this->role = (string) $role;
    }
    public function getRole()
    {
        return $this->role;
    }
}
