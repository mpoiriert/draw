<?php

namespace Draw\Component\Mailer\Email;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;

class CallToActionEmail extends TemplatedEmail
{
    private ?string $callToActionLink = null;

    public array $translationTokens = [];

    public function getContext(): array
    {
        $context = parent::getContext();

        $context['call_to_action_link'] = $this->callToActionLink;

        $context['translation_tokens'] = $this->translationTokens;

        return $context;
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
