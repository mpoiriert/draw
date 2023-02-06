<?php

namespace Draw\Component\OpenApi\Schema;

use JMS\Serializer\Annotation as JMS;

use Symfony\Component\Validator\Constraints as Assert;

#[\Attribute(\Attribute::TARGET_METHOD)]
class Tag
{
    /**
     * The name of the tag.
     *
     * @Assert\NotBlank
     */
    public ?string $name = null;

    /**
     * A short description for the tag.
     * GFM syntax can be used for rich text representation.
     */
    public ?string $description = null;

    /**
     * Additional external documentation for this tag.
     *
     * @JMS\SerializedName("externalDocs")
     */
    public ?ExternalDocumentation $externalDocs = null;

    public function __construct(string $name, ?string $description = null, ?array $externalDocs = null)
    {
        $this->name = $name;
        $this->description = $description;

        if ($externalDocs) {
            $this->externalDocs = new ExternalDocumentation();
            $this->externalDocs->url = $externalDocs['url'];
            $this->externalDocs->description = $externalDocs['description'] ?? null;
        }
    }
}
