<?php

namespace Draw\Bundle\UserBundle\PasswordChangeEnforcer\Entity;

use Doctrine\ORM\Mapping as ORM;
use Draw\Bundle\DoctrineBusMessageBundle\Entity\MessageHolderTrait;
use Draw\Bundle\UserBundle\Entity\SecurityUserInterface;
use Draw\Bundle\UserBundle\PasswordChangeEnforcer\Message\PasswordChangeRequestedMessage;

trait PasswordChangeEnforcerUserTrait
{
    /**
     * @ORM\Column(type="boolean", nullable=false, options={"default":"0"})
     */
    private $needChangePassword = false;

    public function setNeedChangePassword(bool $needChangePassword): void
    {
        if ($this->needChangePassword === $needChangePassword) {
            return;
        }

        $this->needChangePassword = $needChangePassword;
        if ($needChangePassword) {
            if ($this instanceof SecurityUserInterface) {
                $this->setPassword(null);
            }
        }

        if (!trait_exists(MessageHolderTrait::class) || !MessageHolderTrait::useMessageHolderTrait($this)) {
            return;
        }

        if (!$needChangePassword) {
            unset($this->onHoldMessages[PasswordChangeRequestedMessage::class]);

            return;
        }

        $this->onHoldMessages[PasswordChangeRequestedMessage::class] = new PasswordChangeRequestedMessage();
    }

    public function getNeedChangePassword(): bool
    {
        return $this->needChangePassword;
    }
}
