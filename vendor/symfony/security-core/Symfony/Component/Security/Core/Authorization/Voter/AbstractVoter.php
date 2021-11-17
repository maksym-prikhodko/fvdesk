<?php
namespace Symfony\Component\Security\Core\Authorization\Voter;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
abstract class AbstractVoter implements VoterInterface
{
    public function supportsAttribute($attribute)
    {
        return in_array($attribute, $this->getSupportedAttributes());
    }
    public function supportsClass($class)
    {
        foreach ($this->getSupportedClasses() as $supportedClass) {
            if ($supportedClass === $class || is_subclass_of($class, $supportedClass)) {
                return true;
            }
        }
        return false;
    }
    public function vote(TokenInterface $token, $object, array $attributes)
    {
        if (!$object || !$this->supportsClass(get_class($object))) {
            return self::ACCESS_ABSTAIN;
        }
        $vote = self::ACCESS_ABSTAIN;
        foreach ($attributes as $attribute) {
            if (!$this->supportsAttribute($attribute)) {
                continue;
            }
            $vote = self::ACCESS_DENIED;
            if ($this->isGranted($attribute, $object, $token->getUser())) {
                return self::ACCESS_GRANTED;
            }
        }
        return $vote;
    }
    abstract protected function getSupportedClasses();
    abstract protected function getSupportedAttributes();
    abstract protected function isGranted($attribute, $object, $user = null);
}
