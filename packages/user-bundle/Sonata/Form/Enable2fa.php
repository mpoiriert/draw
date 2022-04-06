<?php

namespace Draw\Bundle\UserBundle\Sonata\Form;

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
