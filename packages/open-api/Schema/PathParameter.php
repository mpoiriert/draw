<?php

namespace Draw\Component\OpenApi\Schema;

#[\Attribute(\Attribute::TARGET_METHOD)]
class PathParameter extends Parameter
{
    public function init(): void
    {
        $this->type ??= 'string';
        $this->required = true;
    }
}
