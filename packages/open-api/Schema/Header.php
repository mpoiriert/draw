<?php

namespace Draw\Component\OpenApi\Schema;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @see https://github.com/swagger-api/swagger-spec/blob/master/versions/2.0.md#headerObject
 *
 * @author Martin Poirier Theoret <mpoiriert@gmail.com>
 *
 * @Annotation
 */
class Header
{
    /**
     * A short description of the header.
     */
    public ?string $description = null;

    /**
     * The type of the object. The value MUST be one of "string",.
     *
     * @Assert\NotNull
     * @Assert\Choice({"string", "number", "integer", "boolean", "array"})
     */
    public ?string $type = null;

    /**
     * The extending format for the previously mentioned type. See Data Type Formats for further details.
     */
    public ?string $format = null;

    /**
     * Required if type is "array". Describes the type of items in the array.
     *
     * @Assert\Valid
     */
    public ?Items $items = null;

    /**
     * Determines the format of the array if type array is used. Possible values are:.
     *
     * csv - comma separated values foo,bar.
     * ssv - space separated values foo bar.
     * tsv - tab separated values foo\tbar.
     * pipes - pipe separated values foo|bar.
     *
     * Default value is csv.
     *
     * @Assert\Choice({"csv", "ssv", "tsv", "pipes"})
     *
     * @JMS\SerializedName("collectionFormat")
     */
    public ?string $collectionFormat = null;

    /**
     * Sets a default value to the data type. The type of the value depends on the defined type.
     *
     * @see http://json-schema.org/latest/json-schema-validation.html#anchor101.
     *
     * @var mixed
     *
     * @JMS\Type("Draw\Component\OpenApi\Schema\MixedData")
     */
    public $default;

    /**
     * @see  http://json-schema.org/latest/json-schema-validation.html#anchor17
     */
    public ?int $maximum = null;

    /**
     * @see http://json-schema.org/latest/json-schema-validation.html#anchor17
     *
     * @JMS\SerializedName("exclusiveMaximum")
     */
    public ?bool $exclusiveMaximum = null;

    /**
     * @see http://json-schema.org/latest/json-schema-validation.html#anchor21
     */
    public ?int $minimum = null;

    /**
     * @see http://json-schema.org/latest/json-schema-validation.html#anchor21
     *
     * @JMS\SerializedName("exclusiveMinimum")
     */
    public ?bool $exclusiveMinimum = null;

    /**
     * @see http://json-schema.org/latest/json-schema-validation.html#anchor26
     *
     * @JMS\SerializedName("maxLength")
     */
    public ?int $maxLength = null;

    /**
     * @see http://json-schema.org/latest/json-schema-validation.html#anchor29
     *
     * @JMS\SerializedName("minLength")
     */
    public ?int $minLength = null;

    /**
     * @see http://json-schema.org/latest/json-schema-validation.html#anchor33
     */
    public ?string $pattern = null;

    /**
     * @see http://json-schema.org/latest/json-schema-validation.html#anchor42
     *
     * @JMS\SerializedName("maxItems")
     */
    public ?int $maxItems = null;

    /**
     * @see http://json-schema.org/latest/json-schema-validation.html#anchor45
     *
     * @JMS\SerializedName("minItems")
     */
    public ?int $minItems = null;

    /**
     * @see http://json-schema.org/latest/json-schema-validation.html#anchor49
     *
     * @JMS\SerializedName("uniqueItems")
     */
    public ?bool $uniqueItems = null;

    /**
     * @see http://json-schema.org/latest/json-schema-validation.html#anchor76
     *
     * @JMS\Type("array<Draw\Component\OpenApi\Schema\MixedData>")
     */
    public ?array $enum = null;

    /**
     * @see http://json-schema.org/latest/json-schema-validation.html#anchor14
     *
     * @JMS\SerializedName("multipleOf")
     */
    public ?int $multipleOf = null;

    /**
     * @JMS\PreSerialize
     */
    public function preSerialize(): void
    {
        $this->default = MixedData::convert($this->default);
        $this->enum = MixedData::convert($this->enum, true);
    }
}
