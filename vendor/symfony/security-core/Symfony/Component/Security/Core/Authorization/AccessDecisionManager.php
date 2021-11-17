<?php
namespace Symfony\Component\Security\Core\Authorization;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
class AccessDecisionManager implements AccessDecisionManagerInterface
{
    const STRATEGY_AFFIRMATIVE = 'affirmative';
    const STRATEGY_CONSENSUS = 'consensus';
    const STRATEGY_UNANIMOUS = 'unanimous';
    private $voters;
    private $strategy;
    private $allowIfAllAbstainDecisions;
    private $allowIfEqualGrantedDeniedDecisions;
    public function __construct(array $voters, $strategy = self::STRATEGY_AFFIRMATIVE, $allowIfAllAbstainDecisions = false, $allowIfEqualGrantedDeniedDecisions = true)
    {
        if (!$voters) {
            throw new \InvalidArgumentException('You must at least add one voter.');
        }
        $strategyMethod = 'decide'.ucfirst($strategy);
        if (!is_callable(array($this, $strategyMethod))) {
            throw new \InvalidArgumentException(sprintf('The strategy "%s" is not supported.', $strategy));
        }
        $this->voters = $voters;
        $this->strategy = $strategyMethod;
        $this->allowIfAllAbstainDecisions = (bool) $allowIfAllAbstainDecisions;
        $this->allowIfEqualGrantedDeniedDecisions = (bool) $allowIfEqualGrantedDeniedDecisions;
    }
    public function decide(TokenInterface $token, array $attributes, $object = null)
    {
        return $this->{$this->strategy}($token, $attributes, $object);
    }
    public function supportsAttribute($attribute)
    {
        foreach ($this->voters as $voter) {
            if ($voter->supportsAttribute($attribute)) {
                return true;
            }
        }
        return false;
    }
    public function supportsClass($class)
    {
        foreach ($this->voters as $voter) {
            if ($voter->supportsClass($class)) {
                return true;
            }
        }
        return false;
    }
    private function decideAffirmative(TokenInterface $token, array $attributes, $object = null)
    {
        $deny = 0;
        foreach ($this->voters as $voter) {
            $result = $voter->vote($token, $object, $attributes);
            switch ($result) {
                case VoterInterface::ACCESS_GRANTED:
                    return true;
                case VoterInterface::ACCESS_DENIED:
                    ++$deny;
                    break;
                default:
                    break;
            }
        }
        if ($deny > 0) {
            return false;
        }
        return $this->allowIfAllAbstainDecisions;
    }
    private function decideConsensus(TokenInterface $token, array $attributes, $object = null)
    {
        $grant = 0;
        $deny = 0;
        $abstain = 0;
        foreach ($this->voters as $voter) {
            $result = $voter->vote($token, $object, $attributes);
            switch ($result) {
                case VoterInterface::ACCESS_GRANTED:
                    ++$grant;
                    break;
                case VoterInterface::ACCESS_DENIED:
                    ++$deny;
                    break;
                default:
                    ++$abstain;
                    break;
            }
        }
        if ($grant > $deny) {
            return true;
        }
        if ($deny > $grant) {
            return false;
        }
        if ($grant == $deny && $grant != 0) {
            return $this->allowIfEqualGrantedDeniedDecisions;
        }
        return $this->allowIfAllAbstainDecisions;
    }
    private function decideUnanimous(TokenInterface $token, array $attributes, $object = null)
    {
        $grant = 0;
        foreach ($attributes as $attribute) {
            foreach ($this->voters as $voter) {
                $result = $voter->vote($token, $object, array($attribute));
                switch ($result) {
                    case VoterInterface::ACCESS_GRANTED:
                        ++$grant;
                        break;
                    case VoterInterface::ACCESS_DENIED:
                        return false;
                    default:
                        break;
                }
            }
        }
        if ($grant > 0) {
            return true;
        }
        return $this->allowIfAllAbstainDecisions;
    }
}
