<?php

namespace Draw\Component\OpenApi\Schema;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\GroupSequence;
use Symfony\Component\Validator\GroupSequenceProviderInterface;

/**
 * @author Martin Poirier Théoret <mpoiriert@gmail.com>
 */
#[Assert\GroupSequenceProvider]
class Items implements GroupSequenceProviderInterface
{
    /**
     * The internal type of the array. The value MUST be one of "string", "number", "integer", "boolean", or "array".
     * Files and models are not allowed.
     */
    #[Assert\NotNull]
    #[Assert\Choice(['string', 'number', 'integer', 'boolean', 'array', 'file'])]
    public ?string $type = null;

    /**
     * The extending format for the previously mentioned type. See Data Type Formats for further details.
     */
    public ?string $format = null;

    /**
     * Required if type is "array". Describes the type of items in the array.
     */
    #[Assert\Valid]
    public ?Items $items = null;

    /**
     * Determines the format of the array if type array is used. Possible values are:
     *   csv - comma separated values foo,bar.
     *   ssv - space separated values foo bar.
     *   tsv - tab separated values foo\tbar.
     *   pipes - pipe separated values foo|bar.
     *
     *   Default value is csv.
     */
    #[Assert\Choice(['csv', 'ssv', 'tsv', 'pipes'])]
    #[JMS\SerializedName('collectionFormat')]
    public ?string $collectionFormat = null;

    /**
     * Sets a default value to the parameter. The type of the value depends on the defined type.
     *
     * @see http://json-schema.org/latest/json-schema-validation.html#anchor101
     */
    #[JMS\Type(MixedData::class)]
    public mixed $default = null;

    /**
     * @see  http://json-schema.org/latest/json-schema-validation.html#anchor17
     */
    public ?int $maximum = null;

    /**
     * @see http://json-schema.org/latest/json-schema-validation.html#anchor17
     */
    #[JMS\SerializedName('exclusiveMaximum')]
    public ?bool $exclusiveMaximum = null;

    /**
     * @see http://json-schema.org/latest/json-schema-validation.html#anchor21
     */
    public ?int $minimum = null;

    /**
     * @see http://json-schema.org/latest/json-schema-validation.html#anchor21
     */
    #[JMS\SerializedName('exclusiveMinimum')]
    public ?bool $exclusiveMinimum = null;

    /**
     * @see http://json-schema.org/latest/json-schema-validation.html#anchor26
     */
    #[JMS\SerializedName('maxLength')]
    public ?int $maxLength = null;

    /**
     * @see http://json-schema.org/latest/json-schema-validation.html#anchor29
     */
    #[JMS\SerializedName('minLength')]
    public ?int $minLength = null;

    /**
     * @see http://json-schema.org/latest/json-schema-validation.html#anchor33
     */
    public ?string $pattern = null;

    /**
     * @see http://json-schema.org/latest/json-schema-validation.html#anchor42
     */
    #[JMS\SerializedName('maxItems')]
    public ?int $maxItems = null;

    /**
     * @see http://json-schema.org/latest/json-schema-validation.html#anchor45
     */
    #[JMS\SerializedName('minItems')]
    public ?int $minItems = null;

    /**
     * @see http://json-schema.org/latest/json-schema-validation.html#anchor49
     */
    #[JMS\SerializedName('uniqueItems')]
    public ?bool $uniqueItems = null;

    /**
     * @see http://json-schema.org/latest/json-schema-validation.html#anchor76
     */
    #[JMS\Type('array<'.MixedData::class.'>')]
    public ?array $enum = null;

    /**
     * @see http://json-schema.org/latest/json-schema-validation.html#anchor14
     */
    #[Assert\GreaterThan(0)]
    #[JMS\SerializedName('multipleOf')]
    public ?int $multipleOf = null;

    public function __construct(
        ?string $type = null,
        ?string $format = null,
        ?self $items = null,
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
    ) {
        $this->type = $type;
        $this->format = $format;
        $this->items = $items;
        $this->collectionFormat = $collectionFormat;
        $this->default = $default;
        $this->maximum = $maximum;
        $this->exclusiveMaximum = $exclusiveMaximum;
        $this->minimum = $minimum;
        $this->exclusiveMinimum = $exclusiveMinimum;
        $this->maxLength = $maxLength;
        $this->minLength = $minLength;
        $this->pattern = $pattern;
        $this->maxItems = $maxItems;
        $this->minItems = $minItems;
        $this->uniqueItems = $uniqueItems;
        $this->enum = $enum;
        $this->multipleOf = $multipleOf;
    }

    #[JMS\PreSerialize]
    public function preSerialize(): void
    {
        $this->default = MixedData::convert($this->default);
        $this->enum = MixedData::convert($this->enum, true);
    }

    public function getGroupSequence(): array|GroupSequence
    {
        $groups = ['Items'];

        if ($this->type) {
            $groups[] = $this->type;
        }

        return $groups;
    }
}
