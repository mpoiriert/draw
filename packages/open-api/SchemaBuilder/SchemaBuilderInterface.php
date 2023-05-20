<?php

namespace Draw\Component\OpenApi\SchemaBuilder;

use Draw\Component\OpenApi\Extraction\ExtractionContextInterface;
use Draw\Component\OpenApi\Schema\Root;

interface SchemaBuilderInterface
{
    public function build(ExtractionContextInterface $extractionContext): Root;
}
