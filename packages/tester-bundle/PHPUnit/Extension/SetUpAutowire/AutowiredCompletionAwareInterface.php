<?php

namespace Draw\Bundle\TesterBundle\PHPUnit\Extension\SetUpAutowire;

interface AutowiredCompletionAwareInterface extends AutowiredInterface
{
    public function postAutowire(): void;
}
