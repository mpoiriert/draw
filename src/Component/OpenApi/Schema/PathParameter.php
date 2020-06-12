<?php

namespace Draw\Component\OpenApi\Schema;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class PathParameter extends Parameter
{
    public function __construct()
    {
        $this->required = true;
    }
}
