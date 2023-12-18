<?php

namespace Draw\Component\Mailer\Recipient;

interface LocalizationAwareInterface
{
    public function getPreferredLocale(): ?string;
}
