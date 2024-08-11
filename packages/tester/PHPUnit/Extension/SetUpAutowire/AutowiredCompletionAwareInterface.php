<?php

namespace Draw\Component\Tester\PHPUnit\Extension\SetUpAutowire;

interface AutowiredCompletionAwareInterface extends AutowiredInterface
{
    public function postAutowire(): void;
}
