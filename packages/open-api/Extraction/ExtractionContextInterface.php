<?php

namespace Draw\Component\OpenApi\Extraction;

use Draw\Component\OpenApi\OpenApi;
use Draw\Component\OpenApi\Schema\Root;

interface ExtractionContextInterface
{
    public function getOpenApi(): OpenApi;

    public function getRootSchema(): Root;

    public function hasParameter(string $name): bool;

    public function getParameter(string $name, $default = null);

    public function getParameters(): array;

    public function setParameter(string $name, $value): void;

    public function removeParameter(string $name): void;

    /**
     * @param array<string,mixed> $parameters
     */
    public function setParameters(array $parameters): void;

    public function createSubContext(): self;
}
