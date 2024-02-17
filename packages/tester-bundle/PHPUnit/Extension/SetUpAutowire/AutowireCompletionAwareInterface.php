<?php

namespace Draw\Bundle\TesterBundle\PHPUnit\Extension\SetUpAutowire;

use Symfony\Component\DependencyInjection\ContainerInterface;

interface AutowireCompletionAwareInterface extends AutowireInterface
{
    public function postAutowire(ContainerInterface $container): void;
}