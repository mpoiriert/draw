<?php

namespace Draw\Bundle\UserBundle\Onboarding\Email;

use Draw\Bundle\PostOfficeBundle\Email\CallToActionEmail;
use Draw\Bundle\UserBundle\Email\ToUserEmailInterface;
use Draw\Bundle\UserBundle\Email\ToUserEmailTrait;

class UserOnboardingEmail extends CallToActionEmail implements ToUserEmailInterface
{
    use ToUserEmailTrait;
}
