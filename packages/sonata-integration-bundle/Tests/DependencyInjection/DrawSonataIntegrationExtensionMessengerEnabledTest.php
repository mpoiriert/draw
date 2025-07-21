<?php

namespace Draw\Bundle\SonataIntegrationBundle\Tests\DependencyInjection;

use Draw\Bundle\SonataIntegrationBundle\DependencyInjection\DrawSonataIntegrationExtension;
use Draw\Bundle\SonataIntegrationBundle\Messenger\Admin\MessengerMessageAdmin;
use Draw\Bundle\SonataIntegrationBundle\Messenger\Controller\MessageController;
use Draw\Bundle\SonataIntegrationBundle\Messenger\EventListener\FinalizeContextQueueCountEventListener;
use Draw\Bundle\SonataIntegrationBundle\Messenger\Security\CanShowMessageVoter;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(DrawSonataIntegrationExtension::class)]
class DrawSonataIntegrationExtensionMessengerEnabledTest extends DrawSonataIntegrationExtensionTest
{
    private static array $queueNames;

    public function getConfiguration(): array
    {
        $configuration = parent::getConfiguration();

        $configuration['messenger'] = [
            'enabled' => true,
            'queue_names' => self::$queueNames = [uniqid('queue-name-')],
        ];

        return $configuration;
    }

    public static function provideServiceDefinitionCases(): iterable
    {
        yield [MessengerMessageAdmin::class];
        yield [MessageController::class];
        yield [FinalizeContextQueueCountEventListener::class];
        yield [CanShowMessageVoter::class];
    }

    public function testMessengerMessageAdminDefinition(): void
    {
        $definition = $this->getContainerBuilder()->getDefinition(MessengerMessageAdmin::class);

        static::assertSame(
            ['setTemplate', ['show', '@DrawSonataIntegration/Messenger/Message/show.html.twig']],
            $definition->getMethodCalls()[0]
        );

        $bindings = $definition->getBindings();

        static::assertSame(
            self::$queueNames,
            $bindings['$queueNames']->getValues()[0]
        );
    }
}
