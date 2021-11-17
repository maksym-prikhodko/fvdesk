<?php
namespace Symfony\Component\Security\Core\Authorization\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
class RoleVoter implements VoterInterface
{
    private $prefix;
    public function __construct($prefix = 'ROLE_')
    {
        $this->prefix = $prefix;
    }
    public function supportsAttribute($attribute)
    {
        return 0 === strpos($attribute, $this->prefix);
    }
    public function supportsClass($class)
    {
        return true;
    }
    public function vote(TokenInterface $token, $object, array $attributes)
    {
        $result = VoterInterface::ACCESS_ABSTAIN;
        $roles = $this->extractRoles($token);
        foreach ($attributes as $attribute) {
            if (!$this->supportsAttribute($attribute)) {
                continue;
            }
            $result = VoterInterface::ACCESS_DENIED;
            foreach ($roles as $role) {
                if ($attribute === $role->getRole()) {
                    return VoterInterface::ACCESS_GRANTED;
                }
            }
        }
        return $result;
    }
    protected function extractRoles(TokenInterface $token)
    {
        return $token->getRoles();
    }
}
