<?php

namespace Draw\Bundle\UserBundle\Email;

use Draw\Component\Mailer\Email\CallToActionEmail;

class UserOnboardingEmail extends CallToActionEmail implements ToUserEmailInterface
{
    use ToUserEmailTrait;
}
