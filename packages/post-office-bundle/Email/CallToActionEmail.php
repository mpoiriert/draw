<?php

namespace Draw\Bundle\PostOfficeBundle\Email;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;

class CallToActionEmail extends TemplatedEmail
{
    private $callToActionLink;

    public function getContext(): array
    {
        $context = parent::getContext();
        $extraContexts[] = [
            'call_to_action_link' => $this->callToActionLink,
        ];

        return array_merge($context, ...$extraContexts);
    }

    public function getCallToActionLink(): ?string
    {
        return $this->callToActionLink;
    }

    public function callToActionLink(string $callToActionLink): self
    {
        $this->callToActionLink = $callToActionLink;

        return $this;
    }
}
