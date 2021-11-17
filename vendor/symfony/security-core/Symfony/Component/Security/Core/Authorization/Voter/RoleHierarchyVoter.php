<?php
namespace Symfony\Component\Security\Core\Authorization\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
class RoleHierarchyVoter extends RoleVoter
{
    private $roleHierarchy;
    public function __construct(RoleHierarchyInterface $roleHierarchy, $prefix = 'ROLE_')
    {
        $this->roleHierarchy = $roleHierarchy;
        parent::__construct($prefix);
    }
    protected function extractRoles(TokenInterface $token)
    {
        return $this->roleHierarchy->getReachableRoles($token->getRoles());
    }
}
