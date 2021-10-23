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
     * @var Operation
     *
     * @Assert\Valid()
     *
     * @JMS\Type("Draw\Component\OpenApi\Schema\Operation")
     */
    public $get;

    /**
     * A definition of a PUT operation on this path.
     *
     * @var Operation
     *
     * @Assert\Valid()
     *
     * @JMS\Type("Draw\Component\OpenApi\Schema\Operation")
     */
    public $put;

    /**
     * A definition of a POST operation on this path.
     *
     * @var Operation
     *
     * @Assert\Valid()
     *
     * @JMS\Type("Draw\Component\OpenApi\Schema\Operation")
     */
    public $post;

    /**
     * A definition of a DELETE operation on this path.
     *
     * @var Operation
     *
     * @Assert\Valid()
     *
     * @JMS\Type("Draw\Component\OpenApi\Schema\Operation")
     */
    public $delete;

    /**
     * A definition of a OPTIONS operation on this path.
     *
     * @var Operation
     *
     * @Assert\Valid()
     *
     * @JMS\Type("Draw\Component\OpenApi\Schema\Operation")
     */
    public $options;

    /**
     * A definition of a HEAD operation on this path.
     *
     * @var Operation
     *
     * @Assert\Valid()
     *
     * @JMS\Type("Draw\Component\OpenApi\Schema\Operation")
     */
    public $head;

    /**
     * A definition of a PATCH operation on this path.
     *
     * @var Operation
     *
     * @Assert\Valid()
     *
     * @JMS\Type("Draw\Component\OpenApi\Schema\Operation")
     */
    public $patch;

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
    public $parameters;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\SerializedName("$ref")
     */
    public $ref;

    /**
     * @return array|Operation[]
     */
    public function getOperations()
    {
        $operations = [];
        foreach ($this as $key => $value) {
            if (!$value instanceof Operation) {
                continue;
            }
            $operations[$key] = $value;
        }

        return $operations;
    }
}
