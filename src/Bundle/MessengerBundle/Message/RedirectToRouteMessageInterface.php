<?php namespace Draw\Bundle\MessengerBundle\Message;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

interface RedirectToRouteMessageInterface extends ResponseGeneratorMessageInterface
{
    public function generateUrlToRedirectTo(UrlGeneratorInterface $urlGenerator): ?string;
}