<?php

namespace Draw\Bundle\SonataExtraBundle\Tests\DependencyInjection;

use Draw\Bundle\SonataExtraBundle\DependencyInjection\DrawSonataExtraExtension;
use Draw\Bundle\SonataExtraBundle\PreventDelete\PreventDeleteRelationLoader;
use Draw\Bundle\SonataExtraBundle\PreventDelete\RelationsDumper;
use Draw\Bundle\SonataExtraBundle\PreventDelete\Security\Voter\PreventDeleteVoter;
use Draw\Bundle\SonataExtraBundle\Security\Handler\CanSecurityHandler;
use Draw\Bundle\SonataExtraBundle\Security\Voter\DefaultCanVoter;
use Symfony\Component\DependencyInjection\Extension\Extension;

/**
 * @internal
 */
class DrawSonataExtraExtensionCanSecurityHandlerEnabledTest extends DrawSonataExtraExtensionTest
{
    public function createExtension(): Extension
    {
        return new DrawSonataExtraExtension();
    }

    public function getConfiguration(): array
    {
        return [
            ...parent::getConfiguration(),
            'can_security_handler' => [
                'enabled' => true,
                'prevent_delete_voter' => [
                    'enabled' => true,
                ],
            ],
        ];
    }

    public static function provideTestHasServiceDefinition(): iterable
    {
        yield from parent::provideTestHasServiceDefinition();
        yield [CanSecurityHandler::class];
        yield [DefaultCanVoter::class];
        yield [PreventDeleteVoter::class];
        yield [PreventDeleteRelationLoader::class];
        yield [RelationsDumper::class];
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
