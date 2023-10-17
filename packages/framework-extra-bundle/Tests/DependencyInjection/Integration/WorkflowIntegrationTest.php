<?php

namespace Draw\Bundle\FrameworkExtraBundle\Tests\DependencyInjection\Integration;

use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\IntegrationInterface;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\WorkflowIntegration;
use Draw\Component\Workflow\EventListener\AddTransitionNameToContextListener;
use Draw\Component\Workflow\EventListener\AddUserToContextListener;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @property WorkflowIntegration $integration
 */
#[CoversClass(WorkflowIntegration::class)]
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

    public static function provideTestLoad(): iterable
    {
        yield [
            [],
            [
                new ServiceConfiguration(
                    'draw.workflow.event_listener.add_user_to_context_listener',
                    [
                        AddUserToContextListener::class,
                    ]
                ),
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
