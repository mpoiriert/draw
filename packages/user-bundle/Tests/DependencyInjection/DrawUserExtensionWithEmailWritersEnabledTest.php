<?php

namespace Draw\Bundle\UserBundle\Tests\DependencyInjection;

use Draw\Bundle\UserBundle\EmailWriter\ForgotPasswordEmailWriter;
use Draw\Bundle\UserBundle\EmailWriter\PasswordChangeRequestedEmailWriter;
use Draw\Bundle\UserBundle\EmailWriter\ToUserEmailWriter;
use Draw\Bundle\UserBundle\EmailWriter\UserOnboardingEmailWriter;

class DrawUserExtensionWithEmailWritersEnabledTest extends DrawUserExtensionTest
{
    public function getConfiguration(): array
    {
        $configuration = parent::getConfiguration();

        $configuration['email_writers'] = [
            'enabled' => true,
            'onboarding' => [
                'expiration_delay' => '+ 24 hours',
            ],
        ];

        return $configuration;
    }

    public static function provideTestHasServiceDefinition(): iterable
    {
        yield from parent::provideTestHasServiceDefinition();
        yield [ForgotPasswordEmailWriter::class];
        yield [UserOnboardingEmailWriter::class];
        yield [PasswordChangeRequestedEmailWriter::class];
        yield [ToUserEmailWriter::class];
    }

    public function testUserOnboardingEmailWriterConfiguration(): void
    {
        static::assertSame(
            '+ 24 hours',
            $this->getContainerBuilder()
                ->getDefinition(UserOnboardingEmailWriter::class)
                ->getArgument('$messageExpirationDelay')
        );
    }
}
