<?php

namespace Draw\Component\OpenApi\Schema;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @author Martin Poirier Theoret <mpoiriert@gmail.com>
 *
 * @Annotation
 */
class Root implements VendorExtensionSupportInterface
{
    use VendorExtensionSupportTrait;

    /**
     * Specifies the Swagger Specification version being used.
     * It can be used by the Swagger UI and other clients to interpret the API listing.
     * The value MUST be "2.0".
     *
     * @Assert\NotBlank
     */
    public ?string $swagger = '2.0';

    /**
     * Provides metadata about the API. The metadata can be used by the clients if needed.
     *
     * @Assert\NotNull
     * @Assert\Valid
     */
    public ?Info $info = null;

    /**
     * The host (name or ip) serving the API. This MUST be the host only and does not include the scheme nor sub-paths.
     * It MAY include a port. If the host is not included, the host serving the documentation is to be used (including the port).
     * The host does not support path templating.
     */
    public ?string $host = null;

    /**
     * The base path on which the API is served, which is relative to the host.
     * If it is not included, the API is served directly under the host.
     * The value MUST start with a leading slash (/). The basePath does not support path templating.
     *
     * @JMS\SerializedName("basePath")
     */
    public ?string $basePath = null;

    /**
     * The transfer protocol of the API.
     * Values MUST be from the list: "http", "https", "ws", "wss".
     * If the schemes is not included, the default scheme to be used is the one used to access the specification.
     *
     * @var string[]
     *
     * @Assert\Choice({"http","https","ws","wss"}, multiple=true)
     * @JMS\Type("array<string>")
     */
    public ?array $schemes = null;

    /**
     * A list of MIME types the APIs can consume.
     * This is global to all APIs but can be overridden on specific API calls.
     * Value MUST be as described under Mime Types.
     *
     * @var string[]
     *
     * @JMS\Type("array<string>")
     */
    public ?array $consumes = null;

    /**
     * A list of MIME types the APIs can produce.
     * This is global to all APIs but can be overridden on specific API calls.
     * Value MUST be as described under Mime Types.
     *
     * @var string[]
     *
     * @JMS\Type("array<string>")
     */
    public ?array $produces = null;

    /**
     * The available paths and operations for the API.
     *
     * @var PathItem[]
     *
     * @Assert\NotBlank()
     * @Assert\Valid()
     * @JMS\Type("array<string,Draw\Component\OpenApi\Schema\PathItem>")
     */
    public ?array $paths = null;

    /**
     * An object to hold data types produced and consumed by operations.
     *
     * @var Schema[]
     *
     * @Assert\Valid()
     * @JMS\Type("array<string,Draw\Component\OpenApi\Schema\Schema>")
     */
    public array $definitions = [];

    /**
     * An object to hold parameters that can be used across operations.
     * This property does not define global parameters for all operations.
     *
     * @var Parameter[]
     *
     * @Assert\Valid()
     * @JMS\Type("array<string,Draw\Component\OpenApi\Schema\BaseParameter>")
     */
    public ?array $parameters = null;

    /**
     * An object to hold responses that can be used across operations.
     * This property does not define global responses for all operations.
     *
     * @var Response[]
     *
     * @Assert\Valid()
     * @JMS\Type("array<string,Draw\Component\OpenApi\Schema\Response>")
     */
    public ?array $responses = null;

    /**
     * Security scheme definitions that can be used across the specification.
     *
     * @var SecurityScheme[]
     *
     * @Assert\Valid()
     * @JMS\Type("array<string,Draw\Component\OpenApi\Schema\SecurityScheme>")
     * @JMS\SerializedName("securityDefinitions")
     */
    public ?array $securityDefinitions = null;

    /**
     * A declaration of which security schemes are applied for the API as a whole.
     * The list of values describes alternative security schemes that can be used
     * (that is, there is a logical OR between the security requirements).
     * Individual operations can override this definition.
     *
     * @var SecurityRequirement[]
     *
     * @Assert\Valid()
     * @JMS\Type("array<Draw\Component\OpenApi\Schema\SecurityRequirement>")
     */
    public ?array $security = null;

    /**
     * A list of tags used by the specification with additional metadata.
     * The order of the tags can be used to reflect on their order by the parsing tools.
     * Not all tags that are used by the Operation Object must be declared.
     * The tags that are not declared may be organized randomly or based on the tools' logic.
     * Each tag name in the list MUST be unique.
     *
     * @var Tag[]
     *
     * @JMS\Type("array<Draw\Component\OpenApi\Schema\Tag>")
     */
    public ?array $tags = null;

    /**
     * Additional external documentation.
     *
     * @Assert\Valid
     * @JMS\SerializedName("externalDocs")
     */
    public ?ExternalDocumentation $externalDocs = null;

    public function hasDefinition(string $name): bool
    {
        $name = $this->sanitizeReferenceName($name);

        return \array_key_exists($name, $this->definitions);
    }

    public function addDefinition(string $name, Schema $schema): string
    {
        $name = $this->sanitizeReferenceName($name);
        $this->definitions[$name] = $schema;

        return $this->getDefinitionReference($name);
    }

    public function resolveSchema(Schema $schema): Schema
    {
        if (!$schema->ref) {
            return $schema;
        }

        // E.g.: '#' 'definitions' 'ClassName'
        [, $section, $name] = explode('/', $schema->ref, 3);

        return $this->{$section}[$name];
    }

    public function getDefinitionReference(string $name): string
    {
        return '#/definitions/'.$this->sanitizeReferenceName($name);
    }

    public function sanitizeReferenceName(string $name): string
    {
        return trim(str_replace('\\', '.', $name), '.');
    }
}
