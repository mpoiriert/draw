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
     * @Assert\NotBlank
     */
    public ?string $description = null;

    /**
     * @Assert\Valid
     */
    public ?Schema $schema = null;

    /**
     * @Assert\Valid()
     *
     * @JMS\Type("array<string,Draw\Component\OpenApi\Schema\Header>")
     */
    public ?array $headers = null;
}
