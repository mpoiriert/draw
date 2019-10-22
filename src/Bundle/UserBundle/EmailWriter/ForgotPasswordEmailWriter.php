<?php namespace Draw\Bundle\UserBundle\EmailWriter;

use Draw\Bundle\PostOfficeBundle\Email\EmailWriterInterface;
use Draw\Bundle\UserBundle\Email\ForgotPasswordEmail;

class ForgotPasswordEmailWriter implements EmailWriterInterface
{
    public static function getForEmails(): array
    {
        return ['compose'];
    }

    public function compose(ForgotPasswordEmail $forgotPasswordEmail)
    {
        $forgotPasswordEmail
            ->to($forgotPasswordEmail->getEmailAddress());
    }
}