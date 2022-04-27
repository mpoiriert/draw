<?php

namespace Draw\Component\OpenApi\Schema;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @author Martin Poirier Theoret <mpoiriert@gmail.com>
 *
 * @Annotation
 */
class Operation implements VendorExtensionSupportInterface
{
    use VendorExtensionSupportTrait;

    /**
     * A list of tags for API documentation control.
     * Tags can be used for logical grouping of operations by resources or any other qualifier.
     *
     * @var string[]
     *
     * @JMS\Type("array<string>")
     */
    public ?array $tags = null;

    /**
     * A short summary of what the operation does.
     * For maximum readability in the swagger-ui, this field SHOULD be less than 120 characters.
     */
    public ?string $summary = null;

    /**
     * A verbose explanation of the operation behavior. GFM syntax can be used for rich text representation.
     *
     * @see https://help.github.com/articles/github-flavored-markdown/
     */
    public ?string $description = null;

    /**
     * Additional external documentation for this operation.
     *
     * @Assert\Valid()
     */
    public ?ExternalDocumentation $externalDocs = null;

    /**
     * A friendly name for the operation.
     * The id MUST be unique among all operations described in the API.
     * Tools and libraries MAY use the operation id to uniquely identify an operation.
     *
     * @JMS\SerializedName("operationId")
     */
    public ?string $operationId = null;

    /**
     * A list of MIME types the operation can consume.
     * This overrides the [consumes](#swaggerConsumes) definition at the Swagger Object.
     * An empty value MAY be used to clear the global definition. Value MUST be as described under Mime Types.
     *
     * @var string[]
     *
     * @JMS\Type("array<string>")
     */
    public ?array $consumes = null;

    /**
     * A list of MIME types the operation can produce.
     * This overrides the [produces](#swaggerProduces) definition at the Swagger Object.
     * An empty value MAY be used to clear the global definition. Value MUST be as described under Mime Types.
     *
     * @var string[]
     *
     * @JMS\Type("array<string>")
     */
    public ?array $produces = null;

    /**
     * A list of parameters that are applicable for this operation.
     * If a parameter is already defined at the Path Item, the new definition will override it, but can never remove it.
     * The list MUST NOT include duplicated parameters.
     * A unique parameter is defined by a combination of a name and location.
     * The list can use the Reference Object to link to parameters that are defined at the Swagger Object's parameters.
     * There can be one "body" parameter at most.
     *
     * @var BaseParameter[]
     *
     * @Assert\Valid()
     *
     * @JMS\Type("array<Draw\Component\OpenApi\Schema\BaseParameter>")
     */
    public array $parameters = [];

    /**
     * The list of possible responses as they are returned from executing this operation.
     *
     * @var Response[]
     *
     * @Assert\NotNull()
     * @Assert\Valid()
     * @Assert\Count(min=1, minMessage="Operation must have at leas one response.")
     *
     * @JMS\Type("array<string,Draw\Component\OpenApi\Schema\Response>")
     */
    public array $responses = [];

    /**
     * The transfer protocol for the operation. Values MUST be from the list: "http", "https", "ws", "wss".
     * The value overrides the Swagger Object schemes definition.
     *
     * @var string[]
     *
     * @Assert\Choice({"http","https","ws","wss"}, multiple=true)
     * @JMS\Type("array<string>")
     */
    public ?array $schemes = null;

    /**
     * Declares this operation to be deprecated.
     * Usage of the declared operation should be refrained.
     * Default value is false.
     */
    public ?bool $deprecated = null;

    /**
     * A declaration of which security schemes are applied for this operation.
     * The list of values describes alternative security schemes that can be used
     * (that is, there is a logical OR between the security requirements).
     * This definition overrides any declared top-level security.
     * To remove a top-level security declaration, an empty array can be used.
     *
     * @var SecurityRequirement[]
     *
     * @Assert\Valid()
     *
     * @JMS\Type("array<Draw\Component\OpenApi\Schema\SecurityRequirement>")
     */
    public ?array $security = null;
}
