<?php

namespace Draw\Bundle\UserBundle\Email;

use Draw\Component\Mailer\Email\CallToActionEmail;

class PasswordChangeRequestedEmail extends CallToActionEmail implements ToUserEmailInterface
{
    use ToUserEmailTrait;
}
