<?php namespace Draw\Bundle\MessengerBundle\MessageHandler;

use Draw\Bundle\MessengerBundle\Message\RedirectToRouteMessageInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class RedirectToRouteMessageHandler implements MessageHandlerInterface
{
    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function __invoke(RedirectToRouteMessageInterface $message)
    {
        return $message->generateUrlToRedirectTo($this->urlGenerator);
    }
}