<?php

namespace Draw\Component\OpenApi\Schema;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * @author Martin Poirier Theoret <mpoiriert@gmail.com>
 *
 * @Annotation
 */
class ExternalDocumentation
{
    /**
     * A short description of the target documentation. GFM syntax can be used for rich text representation.
     *
     * @see https://help.github.com/articles/github-flavored-markdown/
     */
    public ?string $description = null;

    /**
     * The URL for the target documentation. Value MUST be in the format of a URL.
     *
     * @Assert\NotBlank()
     * @Assert\Url()
     */
    public ?string $url = null;
}
