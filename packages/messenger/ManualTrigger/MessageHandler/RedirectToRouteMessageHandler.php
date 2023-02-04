<?php

namespace Draw\Component\Messenger\ManualTrigger\MessageHandler;

use Draw\Component\Messenger\ManualTrigger\Message\RedirectToRouteMessageInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class RedirectToRouteMessageHandler implements MessageHandlerInterface
{
    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {
    }

    public function __invoke(RedirectToRouteMessageInterface $message): RedirectResponse
    {
        return $message->getRedirectResponse($this->urlGenerator);
    }
}
