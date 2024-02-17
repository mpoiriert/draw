<?php

namespace Draw\Bundle\TesterBundle\PHPUnit\Extension\SetUpAutowire;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::IS_REPEATABLE)]
class AutowireMockProperty
{
    public function __construct(private string $property, private ?string $fromProperty = null)
    {
        $this->fromProperty ??= $property;
    }

    public function getProperty(): string
    {
        return $this->property;
    }

    public function getFromProperty(): string
    {
        return $this->fromProperty;
    }
}