<?php

declare(strict_types=1);

namespace Draw\Component\Tester\PHPUnit\Extension\SetUpAutowire;

#[\Attribute(\Attribute::TARGET_METHOD)]
class AsMock implements PropertyOptionAwareMethodAttributeInterface
{
    /**
     * @param string|array<string> $propertyNames
     */
    public function __construct(
        private string|array $propertyNames,
    ) {
    }

    /**
     * @return array<string>
     */
    public function getPropertyNames(): array
    {
        return (array) $this->propertyNames;
    }

    /**
     * @return array<string, mixed>
     */
    public function getOptions(): array
    {
        return [
            'asMock' => true,
        ];
    }
}
