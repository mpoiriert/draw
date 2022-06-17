<?php

namespace Draw\Component\OpenApi\Schema;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @author Martin Poirier Theoret <mpoiriert@gmail.com>
 *
 * @Annotation
 */
class Info
{
    /**
     * The title of the application.
     *
     * @Assert\NotNull
     */
    public ?string $title = null;

    /**
     * A short description of the application. GFM syntax can be used for rich text representation.
     *
     * @see https://help.github.com/articles/github-flavored-markdown/
     */
    public ?string $description = null;

    /**
     * The Terms of Service for the API.
     *
     * @JMS\SerializedName("termsOfService")
     */
    public ?string $termsOfService = null;

    /**
     * The contact information for the exposed API.
     *
     * @Assert\Valid
     */
    public ?Contact $contact = null;

    /**
     * The license information for the exposed API.
     *
     * @Assert\Valid
     */
    public ?License $license = null;

    /**
     * Provides the version of the application API (not to be confused by the specification version).
     *
     * @Assert\NotBlank
     */
    public ?string $version = null;
}
