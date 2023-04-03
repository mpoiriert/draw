<?php

namespace Draw\Component\OpenApi\Schema;

use Symfony\Component\Validator\Constraints as Assert;

class License
{
    /**
     * The license name used for the API.
     */
    #[Assert\NotBlank]
    public ?string $name = null;

    /**
     * A URL to the license used for the API. MUST be in the format of a URL.
     */
    #[Assert\Url]
    public ?string $url = null;
}
