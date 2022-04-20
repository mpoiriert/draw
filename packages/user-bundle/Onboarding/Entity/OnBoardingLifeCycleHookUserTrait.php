<?php

namespace Draw\Bundle\UserBundle\Onboarding\Entity;

use Doctrine\ORM\Mapping as ORM;
use Draw\Bundle\UserBundle\Entity\SecurityUserInterface;
use Draw\Bundle\UserBundle\Onboarding\Message\NewUserMessage;
use function Draw\Component\Core\use_trait;
use Draw\Component\Messenger\Entity\MessageHolderTrait;

trait OnBoardingLifeCycleHookUserTrait
{
    /**
     * @ORM\PostPersist()
     */
    public function raiseUserCreated(): void
    {
        switch (true) {
            case !$this instanceof SecurityUserInterface:
            case !use_trait($this, MessageHolderTrait::class):
                return;
        }

        $this->onHoldMessages[NewUserMessage::class] = new NewUserMessage($this->getId());
    }
}
