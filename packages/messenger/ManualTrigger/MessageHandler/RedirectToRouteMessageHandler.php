<?php

namespace Draw\Component\Messenger\ManualTrigger\MessageHandler;

use Draw\Component\Messenger\ManualTrigger\Message\RedirectToRouteMessageInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class RedirectToRouteMessageHandler
{
    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {
    }

    #[AsMessageHandler]
    public function handleRedirectToRouteMessage(RedirectToRouteMessageInterface $message): RedirectResponse
    {
        return $message->getRedirectResponse($this->urlGenerator);
    }
}
