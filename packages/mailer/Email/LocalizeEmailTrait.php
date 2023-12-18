<?php

namespace Draw\Component\Mailer\Email;

trait LocalizeEmailTrait
{
    private ?string $locale = null;

    public function setLocale(?string $locale): self
    {
        $this->locale = $locale;

        return $this;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }
}
