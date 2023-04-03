<?php

namespace Draw\Component\OpenApi\Schema;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\GroupSequenceProviderInterface;

/**
 * @author Martin Poirier Theoret <mpoiriert@gmail.com>
 */
#[Assert\GroupSequenceProvider]
class Schema implements GroupSequenceProviderInterface, ValidationConfigurationInterface, VendorExtensionSupportInterface
{
    use VendorExtensionSupportTrait;

    public ?string $format = null;

    public ?string $title = null;

    public ?string $description = null;

    #[JMS\Type(MixedData::class)]
    public mixed $default = null;

    #[JMS\Type('double')]
    public ?float $maximum = null;

    public ?bool $exclusiveMaximum = null;

    #[JMS\Type('double')]
    public ?float $minimum = null;

    #[JMS\SerializedName('exclusiveMinimum')]
    public ?bool $exclusiveMinimum = null;

    #[JMS\SerializedName('maxLength')]
    public ?int $maxLength = null;

    #[JMS\SerializedName('minLength')]
    public ?int $minLength = null;

    public ?string $pattern = null;

    #[JMS\SerializedName('maxItems')]
    public ?int $maxItems = null;

    #[JMS\SerializedName('minItems')]
    public ?int $minItems = null;

    #[JMS\SerializedName('uniqueItems')]
    public ?bool $uniqueItems = null;

    #[JMS\SerializedName('maxProperties')]
    public ?int $maxProperties = null;

    #[JMS\SerializedName('minProperties')]
    public ?int $minProperties = null;

    #[JMS\Type('array<string>')]
    public ?array $required = null;

    #[JMS\Type('array<'.MixedData::class.'>')]
    public ?array $enum = null;

    #[Assert\NotBlank(message: 'You must define a [type] or [ref|allOf]', groups: ['Type'])]
    #[Assert\IsNull(message: 'You cannot define a [type] when [ref|allOf] is defined.', groups: ['Ref'])]
    public ?string $type = null;

    public ?Schema $items = null;

    /**
     * @var Schema[]
     */
    #[JMS\Type('array<'.self::class.'>')]
    #[JMS\SerializedName('allOf')]
    public ?array $allOf = null;

    #[Assert\Valid]
    #[JMS\Type('array<string,'.self::class.'>')]
    public ?array $properties = null;

    #[Assert\Valid]
    #[JMS\SerializedName('additionalProperties')]
    public ?Schema $additionalProperties = null;

    /**
     * Adds support for polymorphism.
     *
     * The discriminator is the schema property name that is used to differentiate between other schema that inherit this schema.
     * The property name used MUST be defined at this schema and it MUST be in the required property list.
     * When used, the value MUST be the name of this schema or any schema that inherits it.
     */
    public ?string $discriminator = null;

    /**
     * Relevant only for Schema "properties" definitions. Declares the property as "read only".
     * This means that it MAY be sent as part of a response but MUST NOT be sent as part of the request.
     * Properties marked as readOnly being true SHOULD NOT be in the required list of the defined schema.
     * Default value is false.
     */
    #[JMS\SerializedName('readOnly')]
    public ?bool $readOnly = null;

    /**
     * This MAY be used only on properties schemas.
     * It has no effect on root schemas.
     * Adds Additional metadata to describe the XML representation format of this property.
     */
    #[Assert\Valid]
    public ?Xml $xml = null;

    /**
     * Additional external documentation.
     */
    #[Assert\Valid]
    #[JMS\SerializedName('externalDocs')]
    public ?ExternalDocumentation $externalDocs = null;

    /**
     * A free-form property to include an example of an instance for this schema.
     */
    #[Assert\Valid]
    #[JMS\Type(MixedData::class)]
    public mixed $example = null;

    #[JMS\SerializedName('$ref')]
    public ?string $ref = null;

    #[JMS\PreSerialize]
    public function preSerialize(): void
    {
        $this->default = MixedData::convert($this->default);
        $this->example = MixedData::convert($this->example);
        $this->enum = MixedData::convert($this->enum, true);
    }

    public function getGroupSequence(): array
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
