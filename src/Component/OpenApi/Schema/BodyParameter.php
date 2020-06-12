<?php

namespace Draw\Component\OpenApi\Schema;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class BodyParameter extends BaseParameter
{
    /**
     * The schema defining the type used for the body parameter.
     *
     * @var Schema
     *
     * @Assert\NotNull()
     * @Assert\Valid()
     * @JMS\Type("Draw\Component\OpenApi\Schema\Schema")
     */
    public $schema;

    public function __construct()
    {
        $this->name = 'body';
    }
}
