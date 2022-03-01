<?php

namespace Draw\Bundle\MessengerBundle\Message;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

trait RedirectToRouteMessageTrait
{
    protected $route;

    protected $urlParameters = [];

    /**
     * @var Response
     */
    protected $response;

    public function generateUrlToRedirectTo(UrlGeneratorInterface $urlGenerator): ?string
    {
        $url = $urlGenerator->generate($this->route, $this->urlParameters, UrlGeneratorInterface::ABSOLUTE_URL);

        $this->response = new RedirectResponse($url);

        return $url;
    }

    public function generateResponse(): Response
    {
        return $this->response;
    }
}
