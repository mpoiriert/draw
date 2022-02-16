<?php

namespace Draw\Bundle\MessengerBundle\CallToAction;

use DateTimeInterface;
use Draw\Bundle\MessengerBundle\Controller\MessageController;
use Draw\Component\Messenger\Stamp\ExpirationStamp;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\TransportMessageIdStamp;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class MessageUrlGenerator
{
    private $messageBus;

    private $urlGenerator;

    public function __construct(MessageBusInterface $messageBus, UrlGeneratorInterface $urlGenerator)
    {
        $this->messageBus = $messageBus;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @param string|null $type The type of message. Can be use to customise error message.
     *
     * @return string The absolute URL to activate the message
     */
    public function generateLink($message, DateTimeInterface $expiration, string $type = null): string
    {
        $parameters = [
            MessageController::MESSAGE_ID_PARAMETER_NAME => $this->messageBus
                ->dispatch(
                    $message,
                    [new ExpirationStamp($expiration)]
                )
                ->last(TransportMessageIdStamp::class)
                ->getId(),
        ];

        if ($type) {
            $parameters['type'] = $type;
        }

        return $this->urlGenerator->generate('message_click', $parameters, UrlGeneratorInterface::ABSOLUTE_URL);
    }
}
