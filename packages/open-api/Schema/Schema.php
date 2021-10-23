<?php

namespace Draw\Component\OpenApi\Schema;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\GroupSequenceProviderInterface;

/**
 * @author Martin Poirier Theoret <mpoiriert@gmail.com>
 *
 * @Assert\GroupSequenceProvider()
 */
class Schema implements GroupSequenceProviderInterface, ValidationConfigurationInterface, VendorExtensionSupportInterface
{
    use VendorExtensionSupportTrait;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    public $format;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    public $title;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    public $description;

    /**
     * @var mixed
     *
     * @JMS\Type("Draw\Component\OpenApi\Schema\Mixed")
     */
    public $default;

    /**
     * @var number
     *
     * @JMS\Type("double")
     */
    public $maximum;

    /**
     * @var bool
     *
     * @JMS\Type("boolean")
     */
    public $exclusiveMaximum;

    /**
     * @var number
     *
     * @JMS\Type("double")
     */
    public $minimum;

    /**
     * @var bool
     *
     * @JMS\Type("boolean")
     * @JMS\SerializedName("exclusiveMinimum")
     */
    public $exclusiveMinimum;

    /**
     * @var int
     *
     * @JMS\Type("integer")
     * @JMS\SerializedName("maxLength")
     */
    public $maxLength;

    /**
     * @var int
     *
     * @JMS\Type("integer")
     * @JMS\SerializedName("minLength")
     */
    public $minLength;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    public $pattern;

    /**
     * @var int
     *
     * @JMS\Type("integer")
     * @JMS\SerializedName("maxItems")
     */
    public $maxItems;

    /**
     * @var int
     *
     * @JMS\Type("integer")
     * @JMS\SerializedName("minItems")
     */
    public $minItems;

    /**
     * @var bool
     *
     * @JMS\Type("boolean")
     * @JMS\SerializedName("uniqueItems")
     */
    public $uniqueItems;

    /**
     * @var int
     *
     * @JMS\Type("integer")
     * @JMS\SerializedName("maxProperties")
     */
    public $maxProperties;

    /**
     * @var int
     *
     * @JMS\Type("integer")
     * @JMS\SerializedName("minProperties")
     */
    public $minProperties;

    /**
     * @var string[]
     *
     * @JMS\Type("array<string>")
     */
    public $required;

    /**
     * @var mixed[]
     *
     * @JMS\Type("array<Draw\Component\OpenApi\Schema\Mixed>")
     */
    public $enum;

    /**
     * @var string
     *
     * @JMS\Type("string")
     *
     * @Assert\NotBlank(groups={"Type"}, message="You must define a [type] or [ref|allOf]")
     * @Assert\IsNull(groups={"Ref"}, message="You cannot define a [type] when [ref|allOf] is defined.")
     */
    public $type;

    /**
     * @var Schema
     *
     * @JMS\Type("Draw\Component\OpenApi\Schema\Schema")
     */
    public $items;

    /**
     * @var Schema[]
     *
     * @JMS\Type("array<Draw\Component\OpenApi\Schema\Schema>")
     * @JMS\SerializedName("allOf")
     */
    public $allOf;

    /**
     * @var Schema[]
     *
     * @Assert\Valid()
     *
     * @JMS\Type("array<string,Draw\Component\OpenApi\Schema\Schema>")
     */
    public $properties;

    /**
     * @var Schema
     *
     * @Assert\Valid()
     *
     * @JMS\Type("Draw\Component\OpenApi\Schema\Schema")
     * @JMS\SerializedName("additionalProperties")
     */
    public $additionalProperties;

    /**
     * Adds support for polymorphism.
     *
     * The discriminator is the schema property name that is used to differentiate between other schema that inherit this schema.
     * The property name used MUST be defined at this schema and it MUST be in the required property list.
     * When used, the value MUST be the name of this schema or any schema that inherits it.
     *
     * @var string
     */
    public $discriminator;

    /**
     * Relevant only for Schema "properties" definitions. Declares the property as "read only".
     * This means that it MAY be sent as part of a response but MUST NOT be sent as part of the request.
     * Properties marked as readOnly being true SHOULD NOT be in the required list of the defined schema.
     * Default value is false.
     *
     * @var bool
     *
     * @JMS\Type("boolean")
     * @JMS\SerializedName("readOnly")
     */
    public $readOnly;

    /**
     * This MAY be used only on properties schemas.
     * It has no effect on root schemas.
     * Adds Additional metadata to describe the XML representation format of this property.
     *
     * @var Xml
     *
     * @Assert\Valid()
     *
     * @JMS\Type("Draw\Component\OpenApi\Schema\Xml")
     */
    public $xml;

    /**
     * Additional external documentation.
     *
     * @var ExternalDocumentation
     *
     * @Assert\Valid()
     *
     * @JMS\Type("Draw\Component\OpenApi\Schema\ExternalDocumentation")
     * @JMS\SerializedName("externalDocs")
     */
    public $externalDocs;

    /**
     * A free-form property to include a an example of an instance for this schema.
     *
     * @var mixed
     *
     * @Assert\Valid()
     *
     * @JMS\Type("Draw\Component\OpenApi\Schema\Mixed")
     */
    public $example;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\SerializedName("$ref")
     */
    public $ref;

    /**
     * @JMS\PreSerialize()
     */
    public function preSerialize()
    {
        $this->default = Mixed::convert($this->default);
        $this->example = Mixed::convert($this->example);
        $this->enum = Mixed::convert($this->enum, true);
    }

    public function getGroupSequence()
    {
        $groups = ['Schema'];

        if (!$this->ref && !$this->allOf) {
            $groups[] = 'Type';
        } else {
            $groups[] = 'Ref';
        }

        return $groups;
    }
}
