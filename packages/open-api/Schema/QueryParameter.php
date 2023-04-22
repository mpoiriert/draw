<?php

namespace Draw\Component\OpenApi\Schema;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PARAMETER)]
class QueryParameter extends Parameter
{
    /**
     * @var array<Constraint>|Constraint
     */
    #[Serializer\Exclude]
    public array|Constraint $constraints = [];

    public function __construct(
        ?string $name = null,
        ?string $description = null,
        ?bool $required = null,
        ?string $type = null,
        ?string $format = null,
        ?Items $items = null,
        ?string $collectionFormat = null,
        mixed $default = null,
        ?int $maximum = null,
        ?bool $exclusiveMaximum = null,
        ?int $minimum = null,
        ?bool $exclusiveMinimum = null,
        ?int $maxLength = null,
        ?int $minLength = null,
        ?string $pattern = null,
        ?int $maxItems = null,
        ?int $minItems = null,
        ?bool $uniqueItems = null,
        ?array $enum = null,
        ?int $multipleOf = null,
        array|Constraint $constraints = [],
    ) {
        $this->constraints = $constraints;
        parent::__construct(
            $name,
            $description,
            $required,
            $type,
            $format,
            $items,
            $collectionFormat,
            $default,
            $maximum,
            $exclusiveMaximum,
            $minimum,
            $exclusiveMinimum,
            $maxLength,
            $minLength,
            $pattern,
            $maxItems,
            $minItems,
            $uniqueItems,
            $enum,
            $multipleOf,
        );
    }
}
