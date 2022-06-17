<?php

namespace Draw\Component\OpenApi\Configuration;

use Draw\Component\OpenApi\Schema\Header;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ConfigurationAnnotation;

/**
 * @Annotation
 * @Target({"METHOD", "CLASS"})
 */
class Serialization extends ConfigurationAnnotation
{
    protected ?int $statusCode = null;

    protected ?array $serializerGroups = [];

    protected ?bool $serializerEnableMaxDepthChecks = null;

    protected ?string $serializerVersion = null;

    /**
     * @var Header[]|array
     */
    protected array $headers = [];

    protected array $contextAttributes = [];

    public function setStatusCode(?int $statusCode): void
    {
        $this->statusCode = $statusCode;
    }

    public function getStatusCode(): ?int
    {
        return $this->statusCode;
    }

    public function setSerializerGroups(?array $serializerGroups): void
    {
        $this->serializerGroups = $serializerGroups;
    }

    public function getSerializerGroups(): ?array
    {
        return $this->serializerGroups;
    }

    public function setSerializerEnableMaxDepthChecks(?bool $serializerEnableMaxDepthChecks): void
    {
        $this->serializerEnableMaxDepthChecks = $serializerEnableMaxDepthChecks;
    }

    public function getSerializerEnableMaxDepthChecks(): ?bool
    {
        return $this->serializerEnableMaxDepthChecks;
    }

    public function getSerializerVersion(): ?string
    {
        return $this->serializerVersion;
    }

    public function setSerializerVersion(?string $serializerVersion): void
    {
        $this->serializerVersion = $serializerVersion;
    }

    /**
     * @return Header[]
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @param Header[] $headers
     */
    public function setHeaders(array $headers): void
    {
        $this->headers = $headers;
    }

    public function getContextAttributes(): array
    {
        return $this->contextAttributes;
    }

    public function setContextAttributes(array $contextAttributes): void
    {
        $this->contextAttributes = $contextAttributes;
    }

    public function getAliasName(): string
    {
        return 'draw_open_api_serialization';
    }

    public function allowArray(): bool
    {
        return false;
    }
}
