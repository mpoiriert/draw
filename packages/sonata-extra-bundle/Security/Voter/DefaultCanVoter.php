<?php

namespace Draw\Bundle\SonataExtraBundle\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class DefaultCanVoter implements VoterInterface
{
    public function vote(TokenInterface $token, $subject, array $attributes): int
    {
        $vote = VoterInterface::ACCESS_ABSTAIN;
        foreach ($attributes as $attribute) {
            if (0 !== strpos($attribute, 'SONATA_CAN_')) {
                return VoterInterface::ACCESS_ABSTAIN;
            }

            $vote = VoterInterface::ACCESS_GRANTED;
        }

        return $vote;
    }
}
