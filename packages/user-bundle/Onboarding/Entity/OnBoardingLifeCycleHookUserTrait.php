<?php

namespace Draw\Bundle\UserBundle\Onboarding\Entity;

use Doctrine\ORM\Mapping as ORM;
use Draw\Bundle\DoctrineBusMessageBundle\Entity\MessageHolderTrait;
use Draw\Bundle\UserBundle\Entity\SecurityUserInterface;
use Draw\Bundle\UserBundle\Onboarding\Message\NewUserMessage;

/**
 * @property array $onHoldMessages
 */
trait OnBoardingLifeCycleHookUserTrait
{
    /**
     * @ORM\PostPersist()
     */
    public function raiseUserCreated(): void
    {
        switch (true) {
            case !$this instanceof SecurityUserInterface:
            case !trait_exists(MessageHolderTrait::class):
            case !MessageHolderTrait::useMessageHolderTrait($this):
                return;
        }

        $this->onHoldMessages[NewUserMessage::class] = new NewUserMessage($this->getId());
    }
}
