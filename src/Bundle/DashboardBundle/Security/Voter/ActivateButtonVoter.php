<?php namespace Draw\Bundle\DashboardBundle\Security\Voter;

use Draw\Bundle\DashboardBundle\Annotations\Button\Button;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ActivateButtonVoter extends Voter
{
    protected function supports($attribute, $subject)
    {
        switch (true) {
            case $attribute !== 'ACTIVATE':
            case !($subject instanceof Button):
                return false;
        }

        return true;
    }

    /**
     * @param string $attribute
     * @param Button $subject
     * @param TokenInterface $token
     * @return bool
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        foreach($subject->getBehaviours() as $behaviour) {

        }

        return false;
    }
}