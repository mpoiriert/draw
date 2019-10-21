<?php namespace Draw\Bundle\UserBundle\Email;

use Draw\PostOfficeBundle\Email\EmailWriterInterface;

class ForgotPasswordEmailWriter implements EmailWriterInterface
{
    public static function getForEmails(): array
    {
        return ['compose'];
    }

    public function compose(ForgotPasswordEmail $forgotPasswordEmail)
    {
        $forgotPasswordEmail
            ->to($forgotPasswordEmail->getEmailAddress())
            ->subject('You have forgotten your password.');
    }
}