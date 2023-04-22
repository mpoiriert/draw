<?php

namespace Draw\Component\OpenApi\Extraction\Extractor\Constraint;

use Draw\Component\OpenApi\Extraction\ExtractionContextInterface;
use Draw\Component\OpenApi\Schema\Parameter;
use Draw\Component\OpenApi\Schema\Schema;

class ConstraintExtractionContext
{
    public Schema|Parameter $validationConfiguration;

    public ?Schema $classSchema = null;

    /**
     * class or property.
     */
    public ?string $context = null;

    public ?string $propertyName = null;

    public ?ExtractionContextInterface $extractionContext = null;
}
