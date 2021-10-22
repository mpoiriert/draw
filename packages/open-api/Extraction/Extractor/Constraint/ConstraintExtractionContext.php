<?php

namespace Draw\Component\OpenApi\Extraction\Extractor\Constraint;

use Draw\Component\OpenApi\Extraction\ExtractionContextInterface;
use Draw\Component\OpenApi\Schema\Schema;
use Draw\Component\OpenApi\Schema\ValidationConfigurationInterface;

class ConstraintExtractionContext
{
    /**
     * @var ValidationConfigurationInterface
     */
    public $validationConfiguration;

    /**
     * @var Schema
     */
    public $classSchema;

    /**
     * class or property.
     *
     * @var string
     */
    public $context;

    /**
     * @var string
     */
    public $propertyName;

    /**
     * @var ExtractionContextInterface
     */
    public $extractionContext;
}
