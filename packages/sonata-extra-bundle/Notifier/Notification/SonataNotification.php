<?php

namespace Draw\Bundle\SonataExtraBundle\Notifier\Notification;

use Symfony\Component\Notifier\Notification\Notification;

class SonataNotification extends Notification implements SonataNotificationInterface
{
    private string $sonataFlashType = 'success';

    public static function success(string $message): self
    {
        return (new self($message))->setSonataFlashType('success');
    }

    public static function error(string $message): self
    {
        return (new self($message))->setSonataFlashType('error');
    }

    public function __construct(string $subject = '', array $channels = ['sonata'])
    {
        parent::__construct($subject, $channels);
    }

    public function getSonataFlashType(): string
    {
        return $this->sonataFlashType;
    }

    public function setSonataFlashType(string $sonataFlashType): static
    {
        $this->sonataFlashType = $sonataFlashType;

        return $this;
    }
}
