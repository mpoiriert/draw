<?php

namespace Draw\Bundle\UserBundle\Tests\DependencyInjection;

use Draw\Bundle\UserBundle\Onboarding\EmailWriter\UserOnboardingEmailWriter;
use Draw\Bundle\UserBundle\Onboarding\MessageHandler\NewUserSendEmailMessageHandler;
use Draw\Bundle\UserBundle\Tests\Fixtures\Entity\User;

class DrawUserExtensionWithOnboardingEnabledTest extends DrawUserExtensionTest
{
    public function getConfiguration(): array
    {
        return [
            'user_entity_class' => User::class,
            'onboarding' => ['email' => ['enabled' => true]],
        ];
    }

    public function provideTestHasServiceDefinition(): iterable
    {
        yield from parent::provideTestHasServiceDefinition();
        yield [NewUserSendEmailMessageHandler::class];
        yield [UserOnboardingEmailWriter::class];
    }
}
