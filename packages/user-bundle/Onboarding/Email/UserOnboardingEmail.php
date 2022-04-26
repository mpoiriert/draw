<?php

namespace Draw\Bundle\UserBundle\Onboarding\Email;

use Draw\Bundle\UserBundle\Email\ToUserEmailInterface;
use Draw\Bundle\UserBundle\Email\ToUserEmailTrait;
use Draw\Component\Mailer\Email\CallToActionEmail;

class UserOnboardingEmail extends CallToActionEmail implements ToUserEmailInterface
{
    use ToUserEmailTrait;
}
