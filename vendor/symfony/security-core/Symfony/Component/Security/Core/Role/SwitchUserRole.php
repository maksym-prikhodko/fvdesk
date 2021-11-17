<?php
namespace Symfony\Component\Security\Core\Role;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
class SwitchUserRole extends Role
{
    private $source;
    public function __construct($role, TokenInterface $source)
    {
        parent::__construct($role);
        $this->source = $source;
    }
    public function getSource()
    {
        return $this->source;
    }
}
