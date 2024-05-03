<?php

declare(strict_types=1);

namespace Draw\Component\Messenger\Message;

use App\Entity\MessengerMessage;
use Draw\Component\Messenger\DoctrineEnvelopeEntityReference\Message\DoctrineReferenceAwareInterface;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;

class RetryFailedMessageMessage implements DoctrineReferenceAwareInterface
{
    private ?MessengerMessage $message;

    public function __construct(
        MessengerMessage $message,
    ) {
        $this->message = $message;
    }

    public function getMessage(): MessengerMessage
    {
        if (null === $this->message) {
            throw new UnrecoverableMessageHandlingException('Message is not set.');
        }

        return $this->message;
    }

    public function getPropertiesWithDoctrineObject(): array
    {
        return ['message'];
    }
}
