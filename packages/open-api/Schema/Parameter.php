<?php

namespace Draw\Component\OpenApi\Schema;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class Parameter extends BaseParameter implements ValidationConfigurationInterface
{
    /**
     * The type of the parameter. Since the parameter is not located at the request body, it is limited to simple types
     * (that is, not an object). The value MUST be one of "string", "number", "integer", "boolean", "array" or "file".
     * If type is "file", the consumes MUST be either "multipart/form-data" or " application/x-www-form-urlencoded"
     * and the parameter MUST be in "formData".
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
    public ?Items $items = null;

    /**
     * Determines the format of the array if type array is used. Possible values are:
     *   csv - comma separated values foo,bar.
     *   ssv - space separated values foo bar.
     *   tsv - tab separated values foo\tbar.
     *   pipes - pipe separated values foo|bar.
     *   multi - corresponds to multiple parameter instances instead of multiple values for a single instance foo=bar&foo=baz.
     *           This is valid only for parameters in "query" or "formData".
     *
     *   Default value is csv.
     */
    #[Assert\Choice(['csv', 'ssv', 'tsv', 'pipes', 'multi'])]
    #[JMS\SerializedName('collectionFormat')]
    public ?string $collectionFormat = null;

    /**
     * Sets a default value to the parameter. The type of the value depends on the defined type.
     *
     * @see http://json-schema.org/latest/json-schema-validation.html#anchor101d
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
    #[JMS\SerializedName('minimum')]
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
    #[JMS\SerializedName('multipleOf')]
    public ?int $multipleOf = null;

    #[JMS\PreSerialize]
    public function preSerialize(): void
    {
        $this->default = MixedData::convert($this->default);
        $this->enum = MixedData::convert($this->enum, true);
    }
}
