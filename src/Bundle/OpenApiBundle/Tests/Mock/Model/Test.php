<?php

namespace Draw\Bundle\OpenApiBundle\Tests\Mock\Model;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class Test
{
    /**
     * Property description.
     *
     * @Serializer\Type("string")
     * @Serializer\Groups({"Included"})
     *
     * @var string
     */
    private $property;

    /**
     * Property deserialize from body in test.
     *
     * @var string
     *
     * @Serializer\Type("string")
     * @Serializer\Groups({"Included"})
     *
     * @Assert\NotEqualTo("invalidValue")
     */
    private $propertyFromBody;

    /**
     * Will be excluded because of the group.
     *
     * @var string
     *
     * @Serializer\Type("string")
     * @Serializer\Groups({"Excluded"})
     */
    private $propertyGroupExclusion;

    /**
     * @return string
     */
    public function getProperty()
    {
        return $this->property;
    }

    /**
     * @param string $property
     */
    public function setProperty($property)
    {
        $this->property = $property;
    }
}
