<?php

namespace Draw\Component\OpenApi\SchemaBuilder;

use Draw\Component\OpenApi\Schema\Root;

interface SchemaBuilderInterface
{
    public function build(?string $version = null): Root;
}
