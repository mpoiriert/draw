<?php

namespace Draw\Bundle\UserBundle\Tests\DependencyInjection;

use Draw\Bundle\UserBundle\MessageHandler\NewUserSendEmailMessageHandler;

/**
 * @internal
 */
class DrawUserExtensionWithOnboardingEnabledTest extends DrawUserExtensionTest
{
    public function getConfiguration(): array
    {
        $configuration = parent::getConfiguration();
        $configuration['onboarding'] = [
            'enabled' => true,
        ];

        return $configuration;
    }

    public static function provideServiceDefinitionCases(): iterable
    {
        yield from parent::provideServiceDefinitionCases();
        yield [NewUserSendEmailMessageHandler::class];
    }
}
