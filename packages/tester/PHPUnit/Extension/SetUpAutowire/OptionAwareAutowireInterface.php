<?php

declare(strict_types=1);

namespace Draw\Component\Tester\PHPUnit\Extension\SetUpAutowire;

interface OptionAwareAutowireInterface extends AutowireInterface
{
    public function setOptions(array $options): void;
}
