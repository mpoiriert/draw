<?php

namespace Draw\Component\OpenApi\Tests\Mock\Model;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class TestClass
{
    /**
     * Property description.
     *
     * @var string
     */
    #[Serializer\Type('string')]
    #[Serializer\Groups(['Included'])]
    private $property;

    /**
     * Property deserialize from body in test.
     *
     * @var string
     */
    #[Serializer\Type('string')]
    #[Serializer\Groups(['Included'])]
    #[Assert\NotEqualTo('invalidValue')]
    private $propertyFromBody;

    /**
     * Will be excluded because of the group.
     *
     * @var string
     */
    #[Serializer\Type('string')]
    #[Serializer\Groups(['Excluded'])]
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
    public function setProperty($property): void
    {
        $this->property = $property;
    }
}
