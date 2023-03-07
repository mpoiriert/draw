<?php

namespace Draw\Bundle\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Draw\Bundle\UserBundle\Message\NewUserMessage;

use Draw\Component\Messenger\DoctrineMessageBusHook\Entity\MessageHolderTrait;

use function Draw\Component\Core\use_trait;

trait OnBoardingLifeCycleHookUserTrait
{
    #[ORM\PostPersist]
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
