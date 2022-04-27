<?php

namespace Draw\Component\OpenApi\Extraction\Extractor\Constraint;

use Draw\Component\OpenApi\Extraction\ExtractionContextInterface;
use Draw\Component\OpenApi\Schema\Schema;
use Draw\Component\OpenApi\Schema\ValidationConfigurationInterface;

class ConstraintExtractionContext
{
    public ?ValidationConfigurationInterface $validationConfiguration = null;

    public ?Schema $classSchema = null;

    /**
     * class or property.
     */
    public ?string $context = null;

    public ?string $propertyName = null;

    public ?ExtractionContextInterface $extractionContext = null;
}
