<?php

namespace Draw\Component\Workflow\Tests\DependencyInjection;

use Draw\Component\DependencyInjection\Integration\IntegrationInterface;
use Draw\Component\DependencyInjection\Integration\Test\IntegrationTestCase;
use Draw\Component\DependencyInjection\Integration\Test\ServiceConfiguration;
use Draw\Component\Workflow\DependencyInjection\WorkflowIntegration;
use Draw\Component\Workflow\EventListener\AddTransitionNameToContextListener;
use Draw\Component\Workflow\EventListener\AddUserToContextListener;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @property WorkflowIntegration $integration
 *
 * @internal
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

    public static function provideLoadCases(): iterable
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
