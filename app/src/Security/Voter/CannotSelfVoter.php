<?php

namespace App\Security\Voter;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class CannotSelfVoter implements VoterInterface
{
    private const SUPPORTED_ATTRIBUTES = [
        'SONATA_CAN_DELETE',
        'SONATA_CAN_MAKE_ADMIN',
        'SONATA_CAN_ADD_ROLES',
    ];

    public function vote(TokenInterface $token, $subject, array $attributes): int
    {
        foreach ($attributes as $attribute) {
            if (!$this->supports($attribute, $subject)) {
                continue;
            }

            if ($token->getUser() === $subject) {
                return VoterInterface::ACCESS_DENIED;
            }
        }

        return VoterInterface::ACCESS_ABSTAIN;
    }

    private function supports($attribute, $subject): bool
    {
        if (!\in_array($attribute, self::SUPPORTED_ATTRIBUTES, true)) {
            return false;
        }

        if (!$subject instanceof User) {
            return false;
        }

        return true;
    }
}
