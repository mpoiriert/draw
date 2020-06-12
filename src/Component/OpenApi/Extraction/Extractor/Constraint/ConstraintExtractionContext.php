<?php

namespace Draw\Component\OpenApi\Extraction\Extractor\Constraint;

use Draw\Component\OpenApi\Extraction\ExtractionContextInterface;
use Draw\Component\OpenApi\Schema\Schema;

class ConstraintExtractionContext
{
    /**
     * @var Schema
     */
    public $propertySchema;

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
