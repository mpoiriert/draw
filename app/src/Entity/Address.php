<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
class Address
{
    #[ORM\Column(name: 'street', type: 'string', options: ['default' => ''])]
    private ?string $street = '';

    #[ORM\Column(name: 'postal_code', type: 'string', options: ['default' => ''])]
    private ?string $postalCode = '';

    #[ORM\Column(name: 'city', type: 'string', options: ['default' => ''])]
    private ?string $city = '';

    #[ORM\Column(name: 'country', type: 'string', options: ['default' => ''])]
    private ?string $country = '';

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(?string $street): static
    {
        $this->street = $street;

        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(?string $postalCode): static
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): static
    {
        $this->country = $country;

        return $this;
    }
}
