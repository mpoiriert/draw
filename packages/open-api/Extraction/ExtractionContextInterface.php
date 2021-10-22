<?php

namespace Draw\Component\OpenApi\Extraction;

use Draw\Component\OpenApi\OpenApi;

interface ExtractionContextInterface
{
    public function getOpenApi(): OpenApi;

    public function getRootSchema(): \Draw\Component\OpenApi\Schema\Root;

    public function hasParameter($name): bool;

    public function getParameter($name, $default = null);

    public function getParameters(): array;

    public function setParameter($name, $value): void;

    public function removeParameter($name): void;

    public function setParameters(array $parameters): void;

    public function createSubContext(): ExtractionContextInterface;
}
