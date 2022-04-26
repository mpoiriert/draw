<?php

namespace Draw\Component\Mailer\EmailWriter;

use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class DefaultFromEmailWriter implements EmailWriterInterface
{
    private Address $defaultFrom;

    public static function getForEmails(): array
    {
        return [
            'setDefaultFrom' => -255,
        ];
    }

    public function __construct(Address $defaultFrom)
    {
        $this->defaultFrom = $defaultFrom;
    }

    public function setDefaultFrom(Email $email): void
    {
        if (!$email->getFrom()) {
            $email->from($this->defaultFrom);
        }
    }
}
