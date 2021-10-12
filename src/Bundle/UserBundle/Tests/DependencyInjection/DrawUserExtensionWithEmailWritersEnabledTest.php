<?php

namespace Draw\Bundle\UserBundle\Tests\DependencyInjection;

use Draw\Bundle\PostOfficeBundle\DrawPostOfficeBundle;
use Draw\Bundle\UserBundle\DependencyInjection\DrawUserExtension;
use Draw\Bundle\UserBundle\EmailWriter\ForgotPasswordEmailWriter;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;

class DrawUserExtensionWithEmailWritersEnabledTest extends DrawUserExtensionTest
{
    public function createExtension(): Extension
    {
        return new DrawUserExtension();
    }

    public function getConfiguration(): array
    {
        return ['email_writers' => ['enabled' => true]];
    }

    public function provideTestHasServiceDefinition(): iterable
    {
        yield from parent::provideTestHasServiceDefinition();
        yield [ForgotPasswordEmailWriter::class];
    }

    protected function preLoad(array $config, ContainerBuilder $containerBuilder): void
    {
        $containerBuilder->setParameter(
            'kernel.bundles',
            [
                'DrawPostOfficeBundle' => DrawPostOfficeBundle::class,
            ]
        );
    }
}
