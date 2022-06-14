<?php

namespace Draw\Component\Messenger\ManualTrigger;

use DateTimeInterface;
use Draw\Component\Messenger\Expirable\Stamp\ExpirationStamp;
use Draw\Component\Messenger\ManualTrigger\Action\ClickMessageAction;
use Draw\Component\Messenger\ManualTrigger\Message\ManuallyTriggeredInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\TransportMessageIdStamp;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ManuallyTriggeredMessageUrlGenerator
{
    private MessageBusInterface $messageBus;

    private UrlGeneratorInterface $urlGenerator;

    private string $routeName;

    public function __construct(
        MessageBusInterface $messageBus,
        UrlGeneratorInterface $urlGenerator,
        string $routeName = 'draw_messenger.message_click'
    ) {
        $this->messageBus = $messageBus;
        $this->urlGenerator = $urlGenerator;
        $this->routeName = $routeName;
    }

    /**
     * @param string|null $type The type of message. Can be use to customise error message.
     *
     * @return string The absolute URL to activate the message
     */
    public function generateLink(
        ManuallyTriggeredInterface $message,
        DateTimeInterface $expiration,
        string $type = null
    ): string {
        $parameters = [
            ClickMessageAction::MESSAGE_ID_PARAMETER_NAME => $this->messageBus
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

        return $this->urlGenerator->generate($this->routeName, $parameters, UrlGeneratorInterface::ABSOLUTE_URL);
    }
}