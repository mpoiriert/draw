<?php namespace Draw\Component\OpenApi\Extraction;

use Draw\Component\OpenApi\Schema\Root;
use Draw\Component\OpenApi\OpenApi;

class ExtractionContext implements ExtractionContextInterface
{
    /**
     * @var Root
     */
    private $rootSchema;

    /**
     * @var OpenApi
     */
    private $openApi;

    private $parameters = array();

    public function __construct(OpenApi $openApi, Root $rootSchema)
    {
        $this->rootSchema = $rootSchema;
        $this->openApi = $openApi;
    }

    public function getRootSchema(): Root
    {
        return $this->rootSchema;
    }

    public function getOpenApi(): OpenApi
    {
        return $this->openApi;
    }

    public function hasParameter($name): bool
    {
        return array_key_exists($name, $this->parameters);
    }

    public function getParameter($name, $default = null)
    {
        return $this->hasParameter($name) ? $this->parameters[$name] : $default;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function setParameter($name, $value): void
    {
        $this->parameters[$name] = $value;
    }

    public function removeParameter($name): void
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