<?php

namespace Draw\Bundle\UserBundle\Email;

use Draw\Bundle\UserBundle\Entity\SecurityUserInterface;
use Draw\Component\Mailer\Email\LocalizeEmailTrait;
use Draw\Component\Mailer\Recipient\LocalizationAwareInterface;

trait ToUserEmailTrait
{
    use LocalizeEmailTrait;

    private string|int|null $userId = null;

    public function toUser(SecurityUserInterface $user): self
    {
        $this->userId = $user->getId();

        if ($user instanceof LocalizationAwareInterface) {
            $this->setLocale($user->getPreferredLocale());
        }

        return $this;
    }

    public function setUserId(string|int $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    public function getUserId(): string|int|null
    {
        return $this->userId;
    }
}
