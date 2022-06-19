<?php

namespace Draw\Component\OpenApi\Schema;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @author Martin Poirier Theoret <mpoiriert@gmail.com>
 *
 * @Annotation
 */
class PathItem
{
    /**
     * A definition of a GET operation on this path.
     *
     * @Assert\Valid
     */
    public ?Operation $get = null;

    /**
     * A definition of a PUT operation on this path.
     *
     * @Assert\Valid
     */
    public ?Operation $put = null;

    /**
     * A definition of a POST operation on this path.
     *
     * @Assert\Valid
     */
    public ?Operation $post = null;

    /**
     * A definition of a DELETE operation on this path.
     *
     * @Assert\Valid
     */
    public ?Operation $delete = null;

    /**
     * A definition of a OPTIONS operation on this path.
     *
     * @Assert\Valid
     */
    public ?Operation $options = null;

    /**
     * A definition of a HEAD operation on this path.
     *
     * @Assert\Valid
     */
    public ?Operation $head = null;

    /**
     * A definition of a PATCH operation on this path.
     *
     * @Assert\Valid
     */
    public ?Operation $patch = null;

    /**
     * A list of parameters that are applicable for all the operations described under this path.
     * These parameters can be overridden at the operation level, but cannot be removed there.
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
    public ?array $parameters = null;

    /**
     * @JMS\SerializedName("$ref")
     */
    public ?string $ref = null;

    /**
     * @return array{
     *     get?: Operation,
     *     put?: Operation,
     *     post?: Operation,
     *     delete?: Operation,
     *     options?: Operation,
     *     head?: Operation,
     *     patch?: Operation}
     *     |Operation[]
     */
    public function getOperations(): array
    {
        return array_filter([
            'get' => $this->get,
            'put' => $this->put,
            'post' => $this->post,
            'delete' => $this->delete,
            'options' => $this->options,
            'head' => $this->head,
            'patch' => $this->patch,
        ]);
    }
}
