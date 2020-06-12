<?php

namespace Draw\Component\OpenApi\Schema;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @author Martin Poirier Theoret <mpoiriert@gmail.com>
 */
class Response
{
    /**
     * @var string
     *
     * @JMS\Type("string")
     *
     * @Assert\NotNull()
     */
    public $description = '';

    /**
     * @var Schema
     *
     * @Assert\Valid()
     *
     * @JMS\Type("Draw\Component\OpenApi\Schema\Schema")
     */
    public $schema;

    /**
     * @var Header[]
     *
     * @Assert\Valid()
     *
     * @JMS\Type("array<string,Draw\Component\OpenApi\Schema\Header>")
     */
    public $headers;
}
