<?php

namespace Draw\Bundle\SonataIntegrationBundle\User\Form;

use Symfony\Component\Validator\Constraints as Assert;

class Enable2fa
{
    /**
     * @Assert\NotBlank()
     * @Assert\Length(6)
     */
    public ?string $code = null;

    public ?string $totpSecret = null;
}
