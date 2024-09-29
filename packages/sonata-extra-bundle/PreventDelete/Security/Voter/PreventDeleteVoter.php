<?php

namespace Draw\Bundle\SonataExtraBundle\PreventDelete\Security\Voter;

use Doctrine\Persistence\ManagerRegistry;
use Draw\Bundle\SonataExtraBundle\PreventDelete\PreventDeleteRelationLoader;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class PreventDeleteVoter implements VoterInterface
{
    public function __construct(
        private ManagerRegistry $managerRegistry,
        private PreventDeleteRelationLoader $preventDeleteRelationLoader,
    ) {
    }

    public function vote(TokenInterface $token, $subject, array $attributes): int
    {
        if (!$subject) {
            return VoterInterface::ACCESS_ABSTAIN;
        }

        if (!\in_array('SONATA_CAN_DELETE', $attributes, true)) {
            return VoterInterface::ACCESS_ABSTAIN;
        }

        foreach ($this->preventDeleteRelationLoader->getRelationsForObject($subject) as $relation) {
            if ($relation->exists($this->managerRegistry, $subject)) {
                return VoterInterface::ACCESS_DENIED;
            }
        }

        return VoterInterface::ACCESS_ABSTAIN;
    }
}
