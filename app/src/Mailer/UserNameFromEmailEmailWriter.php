<?php

namespace App\Mailer;

use Draw\Component\Mailer\Email\CallToActionEmail;
use Draw\Component\Mailer\EmailWriter\EmailWriterInterface;

class UserNameFromEmailEmailWriter implements EmailWriterInterface
{
    public static function getForEmails(): array
    {
        return ['compose' => -255];
    }

    public function compose(CallToActionEmail $email): void
    {
        $email->translationTokens['%user_name%'] = 'John Doe';
    }
}
