<?php

namespace Draw\Component\OpenApi\Schema;

use Draw\Component\OpenApi\Extraction\Extractor\TypeSchemaExtractor;
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

    /**
     * @return array<QueryParameter>
     */
    public static function fromReflectionMethod(\ReflectionMethod $reflectionMethod): array
    {
        $parameters = [];
        foreach ($reflectionMethod->getParameters() as $reflectionParameter) {
            $attributes = $reflectionParameter
                ->getAttributes(self::class, \ReflectionAttribute::IS_INSTANCEOF);

            foreach ($attributes as $attribute) {
                $attribute = $attribute->newInstance();

                \assert($attribute instanceof self);

                $attribute->name ??= $reflectionParameter->getName();

                $baseType = $attribute->type;

                $type = $reflectionParameter->getType();

                if ($type instanceof \ReflectionNamedType) {
                    $baseType ??= $type->getName();

                    if (null === $attribute->required) {
                        $attribute->required = !$type->allowsNull() && !$reflectionParameter->isDefaultValueAvailable();
                    }
                }

                if ($types = TypeSchemaExtractor::getPrimitiveType($baseType)) {
                    $attribute->type = $types['type'];
                    $attribute->format = $types['format'] ?? null;
                }

                $parameters[] = $attribute;
            }
        }

        return $parameters;
    }
}
