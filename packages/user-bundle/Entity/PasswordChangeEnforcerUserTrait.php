<?php

namespace Draw\Bundle\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Draw\Bundle\UserBundle\Message\PasswordChangeRequestedMessage;
use Draw\Component\Messenger\DoctrineMessageBusHook\Model\MessageHolderTrait;

use function Draw\Component\Core\use_trait;

trait PasswordChangeEnforcerUserTrait
{
    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => '0'])]
    private bool $needChangePassword = false;

    public function requestPasswordChange(): void
    {
        // We set it to false to make sure change password flow is triggered
        $this->setNeedChangePassword(false);
        $this->setNeedChangePassword(true);
    }

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

        if (!use_trait($this, MessageHolderTrait::class)) {
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
