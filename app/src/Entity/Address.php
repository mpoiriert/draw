<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Embeddable
 */
class Address
{
    /**
     * @var string|null
     *
     * @ORM\Column(name="street", type="string", options={"default": ""})
     * @Serializer\Type("string")
     */
    private $street = '';

    /**
     * @var string|null
     *
     * @ORM\Column(name="postal_code", type="string", options={"default": ""})
     * @Serializer\Type("string")
     */
    private $postalCode = '';

    /**
     * @var string|null
     *
     * @ORM\Column(name="city", type="string", options={"default": ""})
     * @Serializer\Type("string")
     */
    private $city = '';

    /**
     * @var string|null
     *
     * @ORM\Column(name="country", type="string", options={"default": ""})
     * @Serializer\Type("string")
     */
    private $country = '';

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(?string $street): void
    {
        $this->street = $street;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(?string $postalCode): void
    {
        $this->postalCode = $postalCode;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): void
    {
        $this->city = $city;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): void
    {
        $this->country = $country;
    }
}
