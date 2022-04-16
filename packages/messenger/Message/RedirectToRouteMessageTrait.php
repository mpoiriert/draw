<?php

namespace Draw\Component\Messenger\Message;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

trait RedirectToRouteMessageTrait
{
    protected ?string $route = null;

    protected array $urlParameters = [];

    public function getRedirectResponse(UrlGeneratorInterface $urlGenerator): RedirectResponse
    {
        $url = $urlGenerator->generate($this->route, $this->urlParameters, UrlGeneratorInterface::ABSOLUTE_URL);

        return new RedirectResponse($url);
    }
}
