<?php

namespace Draw\Component\OpenApi\Schema;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * @Annotation
 *
 * @Target({"METHOD"})
 */
#[\Attribute]
class BodyParameter extends BaseParameter
{
    /**
     * The schema defining the type used for the body parameter.
     *
     * @Assert\NotNull
     * @Assert\Valid
     */
    public ?Schema $schema = null;

    public function __construct()
    {
        $this->name = 'body';
    }
}
