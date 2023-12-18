<?php

namespace Draw\Component\Mailer\Email;

interface LocalizeEmailInterface
{
    public function getLocale(): ?string;
}
