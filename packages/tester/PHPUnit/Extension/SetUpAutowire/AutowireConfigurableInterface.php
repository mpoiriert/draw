<?php

namespace Draw\Component\Tester\PHPUnit\Extension\SetUpAutowire;

use PHPUnit\Runner\Extension\ParameterCollection;

interface AutowireConfigurableInterface extends AutowireInterface
{
    public function configure(ParameterCollection $parameterCollection): void;
}
