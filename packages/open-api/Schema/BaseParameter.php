<?php

namespace Draw\Component\OpenApi\Schema;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @author Martin Poirier Theoret <mpoiriert@gmail.com>
 *
 * @see https://github.com/swagger-api/swagger-spec/blob/master/versions/2.0.md#parameterObject
 */
#[JMS\Discriminator(
    field: 'in',
    map: [
        'body' => BodyParameter::class,
        'header' => HeaderParameter::class,
        'path' => PathParameter::class,
        'query' => QueryParameter::class,
        'formData' => FormDataParameter::class,
        'other' => Parameter::class,
    ],
)]
abstract class BaseParameter
{
    /**
     * The name of the parameter. Parameter names are case sensitive.
     *  - If in is "path", the name field MUST correspond to the associated path segment from the path field in the Paths Object.
     *    See Path Templating for further information.
     *
     *  - For all other cases, the name corresponds to the parameter name used based on the in property.
     */
    #[Assert\NotBlank]
    public ?string $name = null;

    /**
     * A brief description of the parameter. This could contain examples of use.
     * GFM syntax can be used for rich text representation.
     */
    public ?string $description = null;

    /**
     * Determines whether this parameter is mandatory.
     * If the parameter is in "path", this property is required and its value MUST be true.
     * Otherwise, the property MAY be included and its default value is false.
     */
    public ?bool $required = null;

    public function __construct(
        ?string $name = null,
        ?string $description = null,
        ?bool $required = null,
    ) {
        $this->name = $name;
        $this->description = $description;
        $this->required = $required;

        $this->init();
    }

    protected function init(): void
    {
    }

    #[JMS\VirtualProperty]
    #[JMS\SerializedName('in')]
    public function getType(): string
    {
        $striped = str_replace(
            [__NAMESPACE__.'\\', 'Parameter'],
            ['', ''],
            static::class
        );

        return lcfirst($striped);
    }
}
