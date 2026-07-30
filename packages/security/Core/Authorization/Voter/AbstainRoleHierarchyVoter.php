<?php

namespace Draw\Component\Security\Core\Authorization\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\RoleHierarchyVoter;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class AbstainRoleHierarchyVoter extends RoleHierarchyVoter
{
    public function vote(TokenInterface $token, $subject, array $attributes, ?Vote $vote = null): int
    {
        $result = parent::vote($token, $subject, $attributes, $vote);

        return VoterInterface::ACCESS_GRANTED === $result ? VoterInterface::ACCESS_GRANTED : VoterInterface::ACCESS_ABSTAIN;
    }
}
