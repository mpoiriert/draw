<?php

namespace Draw\Bundle\UserBundle\Tests\DependencyInjection;

use Draw\Bundle\UserBundle\EventListener\PasswordChangeEnforcerListener;
use Draw\Bundle\UserBundle\MessageHandler\PasswordChangeRequestedSendEmailMessageHandler;

class DrawUserExtensionWithPasswordChangeEnforcerEnabledTest extends DrawUserExtensionTest
{
    public function getConfiguration(): array
    {
        $configuration = parent::getConfiguration();
        $configuration['password_change_enforcer'] = [
            'enabled' => true,
            'change_password_route' => 'test-route',
        ];

        return $configuration;
    }

    public function provideTestHasServiceDefinition(): iterable
    {
        yield from parent::provideTestHasServiceDefinition();
        yield [PasswordChangeEnforcerListener::class];
        yield [PasswordChangeRequestedSendEmailMessageHandler::class];
    }

    public function testTwoFactorAuthenticationSubscriber(): void
    {
        $this->assertSame(
            'test-route',
            $this->getContainerBuilder()
                ->getDefinition(PasswordChangeEnforcerListener::class)
                ->getArgument('$changePasswordRoute')
        );
    }
}
