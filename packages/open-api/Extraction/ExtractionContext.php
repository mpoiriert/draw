<?php

namespace Draw\Component\OpenApi\Extraction;

use Draw\Component\OpenApi\OpenApi;
use Draw\Component\OpenApi\Schema\Root;

class ExtractionContext implements ExtractionContextInterface
{
    private array $parameters = [];

    public function __construct(private OpenApi $openApi, private Root $rootSchema)
    {
    }

    public function getRootSchema(): Root
    {
        return $this->rootSchema;
    }

    public function getOpenApi(): OpenApi
    {
        return $this->openApi;
    }

    public function hasParameter(string $name): bool
    {
        return \array_key_exists($name, $this->parameters);
    }

    public function getParameter(string $name, $default = null)
    {
        return $this->hasParameter($name) ? $this->parameters[$name] : $default;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @param mixed $value
     */
    public function setParameter(string $name, $value): void
    {
        $this->parameters[$name] = $value;
    }

    public function removeParameter(string $name): void
    {
        unset($this->parameters[$name]);
    }

    public function setParameters(array $parameters): void
    {
        $this->parameters = $parameters;
    }

    public function createSubContext(): ExtractionContextInterface
    {
        return clone $this;
    }
}
