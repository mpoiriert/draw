<?php

namespace Draw\Bundle\SonataIntegrationBundle\Tests\DependencyInjection;

use Draw\Bundle\SonataIntegrationBundle\Messenger\Admin\MessengerMessageAdmin;

/**
 * @covers \Draw\Bundle\SonataIntegrationBundle\DependencyInjection\DrawSonataIntegrationExtension
 */
class DrawSonataIntegrationExtensionMessengerEnabledTest extends DrawSonataIntegrationExtensionTest
{
    private array $queueNames;

    public function getConfiguration(): array
    {
        $configuration = parent::getConfiguration();

        $configuration['messenger'] = [
            'enabled' => true,
            'queue_names' => $this->queueNames = [uniqid('queue-name-')],
        ];

        return $configuration;
    }

    public function provideTestHasServiceDefinition(): iterable
    {
        yield [MessengerMessageAdmin::class];
    }

    public function testMessengerMessageAdminDefinition(): void
    {
        $definition = $this->getContainerBuilder()->getDefinition(MessengerMessageAdmin::class);

        $this->assertSame(
            ['setTemplate', ['show', '@DrawSonataIntegration/Messenger/Message/show.html.twig']],
            $definition->getMethodCalls()[0]
        );

        $methodCall = $definition->getMethodCalls()[1];

        $this->assertSame(
            'inject',
            $methodCall[0]
        );

        $this->assertSame(
            $this->queueNames,
            $methodCall[1]['$queueNames']
        );

        $this->assertSame(
            'draw.messenger.envelope_finder',
            (string) $methodCall[1]['$envelopeFinder']
        );
    }
}
