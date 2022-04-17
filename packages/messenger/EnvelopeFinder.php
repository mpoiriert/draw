<?php

namespace Draw\Component\Messenger;

use Draw\Component\Messenger\Stamp\FindFromTransportStamp;
use Psr\Container\ContainerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\Receiver\ListableReceiverInterface;

class EnvelopeFinder
{
    private ContainerInterface $transportLocator;

    private array $transportNames;

    public function __construct(ContainerInterface $transportLocator, array $transportNames = [])
    {
        $this->transportLocator = $transportLocator;
        $this->transportNames = $transportNames;
    }

    public function findById(string $messageId): ?Envelope
    {
        foreach ($this->transportNames as $transportName) {
            $receiver = $this->transportLocator->get($transportName);
            if (!$receiver instanceof ListableReceiverInterface) {
                continue;
            }

            if ($envelope = $receiver->find($messageId)) {
                return $envelope->with(new FindFromTransportStamp($transportName));
            }
        }

        return null;
    }
}
