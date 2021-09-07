<?php

namespace Draw\Component\OpenApi\Schema;

use JMS\Serializer\Annotation as Serializer;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class QueryParameter extends Parameter
{
    /**
     * @var array
     *
     * @Serializer\Exclude()
     */
    public $constraints = [];
}
