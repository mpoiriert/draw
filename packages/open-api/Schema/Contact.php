<?php

namespace Draw\Component\OpenApi\Schema;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * @author Martin Poirier Theoret <mpoiriert@gmail.com>
 *
 * @Annotation
 */
class Contact
{
    /**
     * The identifying name of the contact person/organization.
     */
    public ?string $name = null;

    /**
     * The URL pointing to the contact information. MUST be in the format of a URL.
     *
     * @Assert\Url
     */
    public ?string $url = null;

    /**
     * The email address of the contact person/organization. MUST be in the format of an email address.
     *
     * @Assert\Email
     */
    public ?string $email = null;
}
