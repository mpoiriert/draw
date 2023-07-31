<?php

namespace Draw\Bundle\SonataIntegrationBundle\Messenger\Security;

use Draw\Component\Messenger\Transport\Entity\DrawMessageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class CanShowMessageVoter implements VoterInterface
{
    public function vote(TokenInterface $token, $subject, array $attributes): int
    {
        if (!$subject instanceof DrawMessageInterface) {
            return VoterInterface::ACCESS_ABSTAIN;
        }

        $vote = VoterInterface::ACCESS_ABSTAIN;
        foreach ($attributes as $attribute) {
            if ('SONATA_CAN_VIEW' === $attribute) {
                if ('9999-12-31' === $subject->getDeliveredAt()?->format('Y-m-d')) {
                    return VoterInterface::ACCESS_DENIED;
                }
            }
        }

        return $vote;
    }
}
