<?php

namespace Draw\Bundle\SonataExtraBundle\Tests\DependencyInjection;

use Draw\Bundle\SonataExtraBundle\DependencyInjection\DrawSonataExtraExtension;
use Draw\Bundle\SonataExtraBundle\Security\Handler\CanSecurityHandler;
use Draw\Bundle\SonataExtraBundle\Security\Voter\DefaultCanVoter;
use Draw\Bundle\SonataExtraBundle\Security\Voter\RelationPreventDeleteCanVoter;
use Symfony\Component\DependencyInjection\Extension\Extension;

class DrawSonataExtraExtensionCanSecurityHandlerEnabledTest extends DrawSonataExtraExtensionTest
{
    public function createExtension(): Extension
    {
        return new DrawSonataExtraExtension();
    }

    public function getConfiguration(): array
    {
        return [
            'can_security_handler' => [
                'enabled' => true,
            ],
        ];
    }

    public function provideTestHasServiceDefinition(): iterable
    {
        yield from parent::provideTestHasServiceDefinition();
        yield [CanSecurityHandler::class];
        yield [DefaultCanVoter::class];
        yield [RelationPreventDeleteCanVoter::class];
    }

    public function testCanSecurityHandlerDefinition(): void
    {
        static::assertSame(
            [
                'sonata.admin.security.handler.role',
                null,
                0,
            ],
            $this->getContainerBuilder()
                ->getDefinition(CanSecurityHandler::class)
                ->getDecoratedService()
        );
    }
}
