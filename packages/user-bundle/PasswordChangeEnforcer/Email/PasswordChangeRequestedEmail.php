<?php

namespace Draw\Bundle\UserBundle\PasswordChangeEnforcer\Email;

use Draw\Bundle\UserBundle\Email\ToUserEmailInterface;
use Draw\Bundle\UserBundle\Email\ToUserEmailTrait;
use Draw\Component\Mailer\Email\CallToActionEmail;

class PasswordChangeRequestedEmail extends CallToActionEmail implements ToUserEmailInterface
{
    use ToUserEmailTrait;
}
