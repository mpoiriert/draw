<?php

namespace Draw\Bundle\FrameworkExtraBundle\Tests\DependencyInjection\Integration;

use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\IntegrationInterface;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\WorkflowIntegration;
use Draw\Component\Workflow\EventListener\AddTransitionNameToContextListener;

/**
 * @covers \Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\WorkflowIntegration
 *
 * @property WorkflowIntegration $integration
 */
class WorkflowIntegrationTest extends IntegrationTestCase
{
    public function createIntegration(): IntegrationInterface
    {
        return new WorkflowIntegration();
    }

    public function getConfigurationSectionName(): string
    {
        return 'workflow';
    }

    public function getDefaultConfiguration(): array
    {
        return [];
    }

    public function provideTestLoad(): iterable
    {
        yield [
            [],
            [
                new ServiceConfiguration(
                    'draw.workflow.event_listener.add_transition_name_to_context_listener',
                    [
                        AddTransitionNameToContextListener::class,
                    ]
                ),
            ],
        ];
    }
}
