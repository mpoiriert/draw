<?php

namespace Draw\Bundle\SonataExtraBundle\Notifier\Channel;

use Draw\Bundle\SonataExtraBundle\Notifier\Notification\SonataNotificationInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Notifier\Channel\ChannelInterface;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\Recipient\RecipientInterface;

#[AutoconfigureTag(
    'notifier.channel',
    ['channel' => 'sonata']
)]
class SonataChannel implements ChannelInterface
{
    public function __construct(private RequestStack $stack)
    {
    }

    public function notify(Notification $notification, RecipientInterface $recipient, ?string $transportName = null): void
    {
        if (null === $request = $this->stack->getCurrentRequest()) {
            return;
        }

        if (!$request->hasSession(true)) {
            return;
        }

        $message = $notification->getSubject();
        if ($notification->getEmoji()) {
            $message = $notification->getEmoji().' '.$message;
        }

        $session = $request->getSession();

        \assert($session instanceof Session);

        $type = $notification instanceof SonataNotificationInterface
            ? 'sonata_flash_'.$notification->getSonataFlashType()
            : 'sonata_flash_success';

        $session->getFlashBag()->add($type, $message);
    }

    public function supports(Notification $notification, RecipientInterface $recipient): bool
    {
        return true;
    }
}
