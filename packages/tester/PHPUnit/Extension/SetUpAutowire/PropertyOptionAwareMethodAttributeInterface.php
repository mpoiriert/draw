<?php

declare(strict_types=1);

namespace Draw\Component\Tester\PHPUnit\Extension\SetUpAutowire;

interface PropertyOptionAwareMethodAttributeInterface
{
    /**
     * @return array<string>
     */
    public function getPropertyNames(): array;

    /**
     * @return array<string, mixed>
     */
    public function getOptions(): array;
}
