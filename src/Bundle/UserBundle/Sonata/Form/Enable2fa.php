<?php

namespace Draw\Bundle\UserBundle\Sonata\Form;

use Symfony\Component\Validator\Constraints as Assert;

class Enable2fa
{
    /**
     * @var string
     * @Assert\NotBlank()
     * @Assert\Length(6)
     */
    public $code;

    /**
     * @var string
     */
    public $totpSecret;
}
