<?php
namespace Symfony\Component\Security\Core\Authorization\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
interface VoterInterface
{
    const ACCESS_GRANTED = 1;
    const ACCESS_ABSTAIN = 0;
    const ACCESS_DENIED = -1;
    public function supportsAttribute($attribute);
    public function supportsClass($class);
    public function vote(TokenInterface $token, $object, array $attributes);
}
