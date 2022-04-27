<?php

namespace Draw\Component\OpenApi\Schema;

use JMS\Serializer\Annotation as JMS;

/**
 * @author Martin Poirier Theoret <mpoiriert@gmail.com>
 */
class SecurityScheme
{
    public ?string $type = null;

    public ?string $description = null;

    public ?string $name = null;

    public ?string $in = null;

    public ?string $flow = null;

    /**
     * @JMS\SerializedName("authorizationUrl")
     */
    public ?string $authorizationUrl = null;

    /**
     * @JMS\SerializedName("tokenUrl")
     */
    public ?string $tokenUrl = null;

    /**
     * @var string[]
     *
     * @JMS\Type("array<string,string>")
     */
    public ?array $scopes = null;
}
