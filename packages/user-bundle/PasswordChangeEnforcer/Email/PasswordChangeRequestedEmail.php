<?php

namespace Draw\Bundle\UserBundle\PasswordChangeEnforcer\Email;

use Draw\Bundle\PostOfficeBundle\Email\CallToActionEmail;
use Draw\Bundle\UserBundle\Email\ToUserEmailInterface;
use Draw\Bundle\UserBundle\Email\ToUserEmailTrait;

class PasswordChangeRequestedEmail extends CallToActionEmail implements ToUserEmailInterface
{
    use ToUserEmailTrait;
}
