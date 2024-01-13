<?php

namespace Draw\Bundle\SonataExtraBundle\Notifier\Notification;

interface SonataNotificationInterface
{
    public function getSonataFlashType(): string;
}
