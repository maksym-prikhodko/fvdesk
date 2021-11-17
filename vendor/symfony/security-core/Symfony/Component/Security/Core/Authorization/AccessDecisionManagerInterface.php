<?php
namespace Symfony\Component\Security\Core\Authorization;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
interface AccessDecisionManagerInterface
{
    public function decide(TokenInterface $token, array $attributes, $object = null);
    public function supportsAttribute($attribute);
    public function supportsClass($class);
}
