<?php

namespace Draw\Component\Messenger\ManualTrigger\Message;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

interface RedirectToRouteMessageInterface
{
    public function getRedirectResponse(UrlGeneratorInterface $urlGenerator): RedirectResponse;
}
